<?php

namespace Claroline\AppBundle\API;

use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\API\Transfer\Adapter\AdapterInterface;
use Claroline\AppBundle\Logger\JsonLogger;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Filesystem\Filesystem;
//should not be here because it's a corebundle dependency
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
     *     "schema"     = @DI\Inject("claroline.api.schema"),
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
        SchemaProvider $schema,
        $logDir,
        TranslatorInterface $translator
      ) {
        $this->adapters = [];
        $this->actions = [];
        $this->om = $om;
        $this->serializer = $serializer;
        $this->logDir = $logDir;
        $this->schema = $schema;
        $this->translator = $translator;
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
     * @param mixed       $options  (currently used to pass the workspace so it' an entity but we might improve it later with an array of parameters)
     */
    public function execute($data, $action, $mimeType, $logFile = null, array $options = [], array $extra = [])
    {
        if (!$logFile) {
            $logFile = uniqid();
        }

        $fs = new FileSystem();
        $fs->mkDir($this->logDir);

        $logFile = $this->logDir.'/'.$logFile;
        $jsonLogger = new JsonLogger($logFile.'.json');

        $executor = $this->getExecutor($action);
        $executor->setLogger($this->logger);
        $adapter = $this->getAdapter($mimeType);

        $data = $this->formatCsvOutput($data);

        $schema = $executor->getSchema();
        //use the translator here
        $jsonLogger->info('Building objects from data...');

        if (array_key_exists('$root', $schema)) {
            $jsonSchema = $this->schema->getSchema($schema['$root']);

            //if we didn't find any but root it's set, it means that is is custom and already defined
            if ($jsonSchema) {
                $explanation = $adapter->explainSchema($jsonSchema, 'create');
                $data = $adapter->decodeSchema($data, $explanation);
            } else {
                $content = $data;
                $data = [];
                $lines = str_getcsv($content, PHP_EOL);
                $header = array_shift($lines);
                $headers = array_filter(
                  str_getcsv($header, ';'),
                    function ($header) {
                        return '' !== trim($header);
                    }
                );

                foreach ($lines as $line) {
                    $properties = array_filter(
                      str_getcsv($line, ';'),
                        function ($property) {
                            return '' !== trim($property);
                        }
                    );
                    $row = [];

                    foreach ($properties as $index => $property) {
                        $row[$headers[$index]] = $property;
                    }

                    $data[] = $row;
                }
            }
        } else {
            foreach ($schema as $prop => $value) {
                //this is for the custom schema defined in the transfer stuff (atm add user to roles for workspace)
                //there is probably a better way to handle this
                if (!$value instanceof \stdClass) {
                    $jsonSchema = $this->schema->getSchema($value, $options, $extra);

                    if ($jsonSchema) {
                        $identifiersSchema[$prop] = $jsonSchema;
                    }
                } else {
                    $identifiersSchema[$prop] = $value;
                }
            }

            $explanation = $adapter->explainIdentifiers($identifiersSchema);
            $data = $adapter->decodeSchema($data, $explanation);
        }

        $data = array_map(function ($el) use ($extra) {
            return array_merge($el, $extra);
        }, $data);

        $i = 0;
        $this->om->startFlushSuite();
        $total = count($data);
        $jsonLogger->info('Executing operations...');

        $jsonLogger->set('total', $total);
        $jsonLogger->set('processed', 0);
        $jsonLogger->set('error', 0);
        $jsonLogger->set('success', 0);
        $jsonLogger->set('data.error', []);
        $jsonLogger->set('data.success', []);
        $loaded = [];
        $loggedSuccess = [];

        //look for duplicatas here
        //JsonSchema defined above
        if (array_key_exists('$root', $schema) && $jsonSchema && isset($jsonSchema->claroline) && isset($jsonSchema->claroline->ids)) {
            $ids = $jsonSchema->claroline->ids;

            //make 3 array with the list of ids
            $dataIds = [];

            foreach ($ids as $id) {
                foreach ($data as $el) {
                    if (isset($el[$id])) {
                        $dataIds[$id][] = $el[$id];
                    }
                }
            }

            $duplicateErrors = [];

            foreach ($dataIds as $property => $dataList) {
                $dataCount = array_count_values($dataList);

                foreach ($dataCount as $value => $count) {
                    if ($count > 1) {
                        $duplicateErrors[$property][] = $value;
                    }
                }
            }

            if (count($duplicateErrors) > 0) {
                foreach ($duplicateErrors as $property => $list) {
                    foreach ($list as $value) {
                        $jsonLogger->push('data.error', [
                          'line' => 'unknown',
                          'value' => "Duplicate {$property} found for value {$value}.",
                        ]);
                    }
                }

                $jsonLogger->set('total', 0);
                $jsonLogger->set('processed', 0);

                return $jsonLogger->get();
            }
        }


        foreach ($data as $el) {
            ++$i;
            $this->log("{$i}/{$total}: ".$this->getActionName($executor));

            try {
                $successData = [];
                $loaded[] = $executor->execute($el, $successData);
                $jsonLogger->info("Operation {$i}/{$total} is a success");
                $jsonLogger->increment('success');
                $loggedSuccess = array_merge_recursive($loggedSuccess, $successData);
                $jsonLogger->set('data.success', $loggedSuccess);
            } catch (\Exception $e) {
                $this->log("Operation {$i}/{$total} failed");
                $this->log($e->getMessage());
                $jsonLogger->info("Operation {$i}/{$total} failed");
                $jsonLogger->increment('error');

                if ($e instanceof InvalidDataException) {
                    $content = [
                      'line' => $i,
                      'value' => $e->getErrors(),
                    ];

                    $jsonLogger->push('data.error', $content);
                } else {
                    $content = [
                      'line' => $i,
                      'value' => $e->getFile().':'.$e->getLine()."\n".$e->getMessage(),
                    ];

                    $jsonLogger->push('data.error', $content);
                }
            }

            if (0 === $i % $executor->getBatchSize()) {
                try {
                    $this->om->forceFlush();

                    foreach ($loaded as $el) {
                        if ($el) {
                            $this->om->detach($el);
                        }
                    }

                    $loaded = [];
                } catch (\Exception $e) {
                    $jsonLogger->info($e->getMessage());
                    $this->log($e->getMessage());
                }
            }

            $jsonLogger->increment('processed');
        }

        $this->om->endFlushSuite();

        return $jsonLogger->get();
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
    public function explainAction($actionName, $format, array $options = [], array $extra = [])
    {
        $adapter = $this->getAdapter($format);
        $action = $this->getExecutor($actionName);
        $schema = $action->getSchema($options, $extra);

        if (array_key_exists('$root', $schema)) {
            $jsonSchema = $this->schema->getSchema($schema['$root']);

            $data = $jsonSchema ?
                $adapter->explainSchema($jsonSchema, $action->getMode()) :
                $adapter->explainSchema($schema['$root'], $action->getMode());
        } else {
            $identifiersSchema = [];

            foreach ($schema as $prop => $value) {
                if ($this->serializer->has($value)) {
                    $identifiersSchema[$prop] = $this->schema->getSchema($value);
                } else {
                    $identifiersSchema[$prop] = $value;
                }
            }

            $data = $adapter->explainIdentifiers($identifiersSchema);
        }

        return (object) array_merge((array) $data, $action->getExtraDefinition($options, $extra));
    }

    /**
     * Returns a list of available actions for a given format (mime type).
     *
     * @param string $format
     *
     * @return array
     */
    public function getAvailableActions($format, array $options = [], array $extra = [])
    {
        $availables = [];

        foreach (array_filter($this->actions, function ($action) use ($format, $options, $extra) {
            return $action->supports($format, $options, $extra);
        }) as $action) {
            $schema = $action->getAction();
            $availables[$schema[0]][$schema[1]] = $this->explainAction($this->getActionName($action), $format, $options, $extra);
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

    private function formatCsvOutput($data)
    {
        // If encoding not UTF-8 then convert it to UTF-8
        $data = $this->stringToUtf8($data);
        $data = str_replace("\r\n", PHP_EOL, $data);
        $data = str_replace("\r", PHP_EOL, $data);
        $data = str_replace("\n", PHP_EOL, $data);

        return $data;
    }

    private function stringToUtf8($string)
    {
        // If encoding not UTF-8 then convert it to UTF-8
        $encoding = $this->detectEncoding($string);
        if ($encoding && 'UTF-8' !== $encoding) {
            $string = iconv($encoding, 'UTF-8', $string);
        }

        return $string;
    }

    /**
     * Detect if encoding is UTF-8, ASCII, ISO-8859-1 or Windows-1252.
     *
     * @param $string
     *
     * @return bool|string
     */
    private function detectEncoding($string)
    {
        static $enclist = ['UTF-8', 'ASCII', 'ISO-8859-1', 'Windows-1252'];
        if (function_exists('mb_detect_encoding')) {
            return mb_detect_encoding($string, $enclist, true);
        }
        $result = false;
        foreach ($enclist as $item) {
            try {
                $sample = iconv($item, $item, $string);
                if (md5($sample) === md5($string)) {
                    $result = $item;
                    break;
                }
            } catch (ContextErrorException $e) {
                unset($e);
            }
        }

        return $result;
    }
}
