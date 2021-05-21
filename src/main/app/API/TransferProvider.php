<?php

namespace Claroline\AppBundle\API;

use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\API\Transfer\Adapter\AdapterInterface;
use Claroline\AppBundle\Log\JsonLogger;
use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
//should not be here because it's a corebundle dependency
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Filesystem\Filesystem;

class TransferProvider implements LoggerAwareInterface
{
    use LoggableTrait;

    /** @var string */
    private $projectDir;
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var string */
    private $logDir;
    /** @var SchemaProvider */
    private $schema;

    /** @var AdapterInterface[] */
    private $adapters = [];
    /** @var AbstractAction[] */
    private $actions = [];

    public function __construct(
        string $projectDir,
        ObjectManager $om,
        SerializerProvider $serializer,
        SchemaProvider $schema,
        string $logDir
      ) {
        $this->projectDir = $projectDir;
        $this->om = $om;
        $this->serializer = $serializer;
        $this->logDir = $logDir;
        $this->schema = $schema;
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
     * @param array       $options  (currently used to pass the workspace so it' an entity but we might improve it later with an array of parameters)
     *
     * @return array
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

        $jsonSchema = null;
        if (array_key_exists('$root', $schema)) {
            $jsonSchema = $this->schema->getSchema($schema['$root']);

            // if we didn't find any but root it's set, it means that is custom and already defined
            if (empty($jsonSchema)) {
                $jsonSchema = json_decode(json_encode($schema['$root']));
            }

            $explanation = $adapter->explainSchema($jsonSchema, 'create');
            $data = $adapter->decodeSchema($data, $explanation);
        } else {
            $identifiersSchema = [];
            foreach ($schema as $prop => $value) {
                //this is for the custom schema defined in the transfer stuff (atm add user to roles for workspace)
                //there is probably a better way to handle this
                if (!$value instanceof \stdClass) {
                    $jsonSchema = $this->schema->getSchema($value, $options);

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

        if (!in_array(Options::FORCE_FLUSH, $executor->getOptions())) {
            $this->om->startFlushSuite();
        }

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

        //look for duplicates here
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

                    foreach ($loaded as $element) {
                        if ($element) {
                            $this->om->detach($element);
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

        if (!in_array(Options::FORCE_FLUSH, $executor->getOptions())) {
            $this->om->endFlushSuite();
        }

        return $jsonLogger->get();
    }

    public function getActionName(AbstractAction $action): string
    {
        return $action->getAction()[0].'_'.$action->getAction()[1];
    }

    /**
     * @param AdapterInterface|AbstractAction $dependency
     *
     * @throws \Exception
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
        $supportedActions = array_filter($this->actions, function (AbstractAction $action) use ($format, $options, $extra) {
            return $action->supports($format, $options, $extra);
        });

        $available = [];
        foreach ($supportedActions as $action) {
            $schema = $action->getAction();
            $available[$schema[0]][$schema[1]] = $this->explainAction($this->getActionName($action), $format, $options, $extra);
        }

        return $available;
    }

    public function getSamples($format, array $options = [], array $extra = [])
    {
        $supportedActions = array_filter($this->actions, function (AbstractAction $action) use ($format, $options, $extra) {
            return $action->supports($format, $options, $extra);
        });

        $samples = [];
        foreach ($supportedActions as $action) {
            $schema = $action->getAction();

            $samples[$schema[0]][$schema[1]] = [];

            $samplesPath = $this->getSamplePath($format, $schema[0], $schema[1]);
            if ($samplesPath) {
                $dir = new \DirectoryIterator($samplesPath);
                foreach ($dir as $fileInfo) {
                    if (!$fileInfo->isDot()) {
                        $samples[$schema[0]][$schema[1]][] = $fileInfo->getFilename();
                    }
                }
            }
        }

        return $samples;
    }

    public function getSamplePath($format, $entity, $action, $filename = null)
    {
        // FIXME : like this I can only define samples in core bundle
        $path = "$this->projectDir/src/main/core/Resources/samples/$entity/$format/valid/$action/";
        if (!empty($filename)) {
            $path .= $filename;
        }

        if (file_exists($path)) {
            return $path;
        }

        return null;
    }

    /**
     * Returns an adapter for a given mime type.
     *
     * @param string $mimeTypes
     *
     * @return AdapterInterface
     *
     * @throws \Exception
     */
    public function getAdapter($mimeTypes)
    {
        $types = explode(';', $mimeTypes);

        foreach ($types as $mimeType) {
            foreach ($this->adapters as $adapter) {
                if (in_array(ltrim($mimeType), $adapter->getMimeTypes())) {
                    return $adapter;
                }
            }
        }

        throw new \Exception('No adapter found for mime type '.$mimeTypes);
    }

    private function formatCsvOutput($data)
    {
        // If encoding not UTF-8 then convert it to UTF-8
        $data = $this->stringToUtf8($data);
        $data = str_replace("\r\n", PHP_EOL, $data);
        $data = str_replace("\r", PHP_EOL, $data);

        // remove BOM if any
        $bom = pack('H*', 'EFBBBF');
        $data = preg_replace("/^$bom/", '', $data);

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
            } catch (\Exception $e) {
                unset($e);
            }
        }

        return $result;
    }
}
