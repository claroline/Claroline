<?php

namespace Claroline\AppBundle\API;

use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\API\Transfer\Adapter\AdapterInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Library\Logger\FileLogger;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.api.transfer")
 */
class TransferProvider
{
    use LoggableTrait;

    /** @var AdapterInterface[] */
    private $adapters;
    /** @var AbstractAction[] */
    private $actions;
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var string */
    private $logDir;
    /** @var TranslatorInterface */
    private $translator;

    /**
     * Crud constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "logDir"     = @DI\Inject("%claroline.param.import_log_dir%"),
     *     "translator" = @DI\Inject("translator")
     * })
     *
     * @param ObjectManager       $om
     * @param SerializerProvider  $serializer
     * @param string              $logDir
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        $logDir,
        TranslatorInterface $translator
      ) {
        $this->adapters = [];
        $this->actions = [];
        $this->om = $om;
        $this->serializer = $serializer;
        $this->logDir = $logDir;
        $this->translator = $translator;
        $this->logger = FileLogger::get(uniqid().'.log', 'claroline.transfer.logger');
    }

    /**
     * Format a list of data for the export.
     *
     * @param string $format  - The mime type we want to change the data into
     * @param array  $data    - The data to format
     * @param array  $options - A list of options
     *
     * @return mixed
     */
    public function format($format, array $data, $options)
    {
        $adapter = $this->getAdapter($format);

        return $adapter->format($data, $options);
    }

    /**
     * @param mixed       $data
     * @param string      $action
     * @param string      $mimeType
     * @param string|null $logFile
     */
    public function execute($data, $action, $mimeType, $logFile = null)
    {
        if (!$logFile) {
            $logFile = uniqid();
        }

        $logFile = $this->logDir.'/'.$logFile.'.log';
        $this->logger = FileLogger::get($logFile, 'claroline.transfer.logger');

        $executor = $this->getExecutor($action);
        $executor->setLogger($this->logger);
        $adapter = $this->getAdapter($mimeType);

        $schema = $executor->getSchema();
        //use the translator here
        $this->log('Building objects from data...');

        if (array_key_exists('$root', $schema)) {
            $jsonSchema = $this->serializer->getSchema($schema['$root']);
            $explanation = $adapter->explainSchema($jsonSchema);
            $data = $adapter->decodeSchema($data, $explanation);
        } else {
            foreach ($schema as $prop => $value) {
                $jsonSchema = $this->serializer->getSchema($value);

                if ($jsonSchema) {
                    $identifiersSchema[$prop] = $jsonSchema;
                }
            }

            $explanation = $adapter->explainIdentifiers($identifiersSchema);
            $data = $adapter->decodeSchema($data, $explanation);
        }

        $i = 0;
        $this->om->startFlushSuite();
        $total = count($data);
        $this->log('Executing operations...');

        foreach ($data as $data) {
            ++$i;
            $this->log("{$i}/{$total}: ".$this->getActionName($executor));
            $executor->execute($data);

            if (0 === $i % $executor->getBatchSize()) {
                $this->om->forceFlush();
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param AbstractAction $action
     *
     * @return string
     */
    public function getActionName(AbstractAction $action)
    {
        return $action->getAction()[0].'_'.$action->getAction()[1];
    }

    /**
     * @param AdapterInterface|AbstractAction $dependency
     */
    public function add($dependency)
    {
        if ($dependency instanceof AdapterInterface) {
            $this->adapters[$dependency->getMimeTypes()[0]] = $dependency;

            return;
        }

        if ($dependency instanceof AbstractAction) {
            $this->actions[$this->getActionName($dependency)] = $dependency;

            return;
        }

        throw new \Exception('Can only add AbstractAction or ActionInterface. Failed to find one for '.get_class($dependency));
    }

    /**
     * Returns the AbstractAction object for an given action.
     *
     * @param string $action
     *
     * @return AbstractAction
     */
    public function getExecutor($action)
    {
        return $this->actions[$action];
    }

    /**
     * Returns the AbstractAction object for an given action.
     *
     * @param string $actionName
     * @param string $format
     *
     * @return mixed|array
     */
    public function explainAction($actionName, $format)
    {
        $adapter = $this->getAdapter($format);
        $action = $this->getExecutor($actionName);

        if (!$action->supports($format)) {
            throw new \Exception('This action is not supported for the '.$format.' format.');
        }

        $schema = $action->getSchema();

        if (array_key_exists('$root', $schema)) {
            $jsonSchema = $this->serializer->getSchema($schema['$root']);

            if ($jsonSchema) {
                return $adapter->explainSchema($jsonSchema, $action->getMode());
            }
        }

        $identifiersSchema = [];

        foreach ($schema as $prop => $value) {
            $jsonSchema = $this->serializer->getSchema($value);

            if ($jsonSchema) {
                $identifiersSchema[$prop] = $jsonSchema;
            }
        }

        return $adapter->explainIdentifiers($identifiersSchema);
    }

    /**
     * Returns a list of available actions for a given format (mime type).
     *
     * @param string $format
     *
     * @return array
     */
    public function getAvailableActions($format)
    {
        $availables = [];

        foreach (array_filter($this->actions, function ($action) use ($format) {
            return $action->supports($format);
        }) as $action) {
            $schema = $action->getAction();
            $availables[$schema[0]][$schema[1]] = $this->explainAction($this->getActionName($action), $format);
        }

        return $availables;
    }

    /**
     * Returns an adapter for a given mime type.
     *
     * @param string $mimeType
     *
     * @return AdapterInterface
     */
    public function getAdapter($mimeTypes)
    {
        $mimeTypes = explode(';', $mimeTypes);

        foreach ($mimeTypes as $mimeType) {
            foreach ($this->adapters as $adapter) {
                if (in_array(ltrim($mimeType), $adapter->getMimeTypes())) {
                    return $adapter;
                }
            }
        }

        throw new \Exception('No adapter found for mime type '.$mimeType);
    }
}
