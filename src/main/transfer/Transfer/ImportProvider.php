<?php

namespace Claroline\TransferBundle\Transfer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SchemaProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Log\JsonLogger;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\TransferBundle\Transfer\Importer\ImporterInterface;
use Symfony\Component\Filesystem\Filesystem;

class ImportProvider extends AbstractProvider
{
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
     * @param mixed $data
     * @param array $options currently only contains Options::WORKSPACE_IMPORT
     * @param array $extra   used to pass the workspace, and custom data coming from the ui form (and defined by ImporterInterface::getExtraDefinition())
     *
     * @return array
     */
    public function execute($data, string $format, string $action, string $logFile, array $options = [], array $extra = [])
    {
        $fs = new FileSystem();
        $fs->mkDir($this->logDir);

        $logFile = $this->logDir.'/'.$logFile;
        $jsonLogger = new JsonLogger($logFile.'.json');

        $executor = $this->getAction($action);
        if (!$executor->supports($format, $options, $extra)) {
            return [];
        }

        $adapter = $this->getAdapter($format);

        $schema = $executor->getSchema();
        //use the translator here
        $jsonLogger->info('Building objects from data...');

        $jsonSchema = null;
        if (array_key_exists('$root', $schema)) {
            if (is_string($schema['$root'])) {
                $jsonSchema = $this->schema->getSchema($schema['$root']);
            }

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

        $total = count($data);
        $jsonLogger->info('Executing operations...');

        $jsonLogger->set('total', $total);
        $jsonLogger->set('processed', 0);
        $jsonLogger->set('error', 0);
        $jsonLogger->set('success', 0);
        $jsonLogger->set('data.error', []);
        $jsonLogger->set('data.success', []);

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

        $this->om->startFlushSuite();

        $i = 0;
        foreach ($data as $el) {
            ++$i;

            try {
                $lineLog = $executor->execute($el);

                $jsonLogger->info("Operation {$i}/{$total} is a success");
                $jsonLogger->increment('success');
                $loggedSuccess = array_merge_recursive($loggedSuccess, $lineLog);
                $jsonLogger->set('data.success', $loggedSuccess);
            } catch (\Exception $e) {
                $jsonLogger->info("Operation {$i}/{$total} failed");
                $jsonLogger->increment('error');

                if ($e instanceof InvalidDataException) {
                    $jsonLogger->push('data.error', [
                        'line' => $i,
                        'value' => $e->getErrors(),
                    ]);
                } else {
                    $jsonLogger->push('data.error', [
                        'line' => $i,
                        'value' => $e->getFile().':'.$e->getLine()."\n".$e->getMessage(),
                    ]);
                }
            }

            if (0 === $i % $executor->getBatchSize()) {
                try {
                    $this->om->forceFlush();
                } catch (\Exception $e) {
                    $jsonLogger->info($e->getMessage());
                }
            }

            $jsonLogger->increment('processed');
        }

        try {
            $this->om->endFlushSuite();
        } catch (\Exception $e) {
            $jsonLogger->info($e->getMessage());
        }

        return $jsonLogger->get();
    }

    /**
     * Returns a list of available importers for a given format (mime type).
     */
    public function getAvailableActions(string $format, ?array $options = [], ?array $extra = []): array
    {
        $supportedActions = array_filter(iterator_to_array($this->actions), function (ImporterInterface $action) use ($format, $options, $extra) {
            return $action->supports($format, $options, $extra);
        });

        $available = [];
        foreach ($supportedActions as $action) {
            $schema = $action::getAction();
            $available[$schema[0]][$schema[1]] = $this->explainAction($this->getActionName($action), $format, $options, $extra);
        }

        return $available;
    }

    public function getSamples(string $format, array $options = [], array $extra = []): array
    {
        $supportedActions = array_filter(iterator_to_array($this->actions), function (ImporterInterface $action) use ($format, $options, $extra) {
            return $action->supports($format, $options, $extra);
        });

        $samples = [];
        foreach ($supportedActions as $action) {
            $schema = $action::getAction();

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

    public function getSamplePath($format, $entity, $action, $filename = null): ?string
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

    private function explainAction(string $actionName, string $format, array $options = [], array $extra = [])
    {
        $action = $this->getAction($actionName);
        $schema = $this->getExplanation($actionName, $format, $options, $extra);

        return (object) array_merge((array) $schema, $action->getExtraDefinition($options, $extra));
    }

    private function getExplanation(string $actionName, string $format, array $options = [], array $extra = [])
    {
        $adapter = $this->getAdapter($format);
        $action = $this->getAction($actionName);
        $schema = $action->getSchema($options, $extra);

        if (array_key_exists('$root', $schema)) {
            if (is_string($schema['$root'])) {
                $jsonSchema = $this->schema->getSchema($schema['$root']);
                if ($jsonSchema) {
                    return $adapter->explainSchema($jsonSchema, $action->getMode());
                }
            }

            return $adapter->explainSchema($schema['$root'], $action->getMode());
        }

        $identifiersSchema = [];
        foreach ($schema as $prop => $value) {
            if ($this->serializer->has($value)) {
                $identifiersSchema[$prop] = $this->schema->getSchema($value);
            } else {
                $identifiersSchema[$prop] = $value;
            }
        }

        return $adapter->explainIdentifiers($identifiersSchema);
    }
}
