<?php

namespace UJM\ExoBundle\Library\Json;

use JMS\DiExtraBundle\Annotation as DI;
use JVal\Utils;
use JVal\Validator as SchemaValidator;

/**
 * Loads and validates JSON Schemas.
 *
 * @DI\Service("ujm_exo.library.json_schema")
 */
class JsonSchema
{
    private $baseUri = 'http://json-quiz.github.io/json-quiz/schemas';

    /**
     * @var string
     */
    private $kernelDir;

    /**
     * List of loaded schemas.
     *
     * @var array
     */
    private $schemas = [];

    /**
     * @var SchemaValidator
     */
    private $validator = null;

    /**
     * JsonSchema constructor.
     *
     * @param string $kernelDir
     *
     * @DI\InjectParams({
     *     "kernelDir" = @DI\Inject("%kernel.root_dir%")
     * })
     */
    public function __construct($kernelDir)
    {
        $this->kernelDir = $kernelDir;
    }

    /**
     * Validates data against the schema located at URI.
     *
     * @param mixed  $data - the data to validate
     * @param string $uri  - the URI of the schema to use
     *
     * @return array
     */
    public function validate($data, $uri)
    {
        return $this->getValidator()->validate($data, $this->getSchema($uri));
    }

    /**
     * Get schema validator.
     *
     * @return SchemaValidator
     */
    private function getValidator()
    {
        if (null === $this->validator) {
            $hook = function ($uri) {
                return $this->uriToFile($uri);
            };

            $this->validator = SchemaValidator::buildDefault($hook);
        }

        return $this->validator;
    }

    /**
     * Loads schema from URI.
     *
     * @param $uri
     *
     * @return mixed
     *
     * @throws \JVal\Exception\JsonDecodeException
     */
    private function getSchema($uri)
    {
        if (empty($this->schemas[$uri])) {
            $this->schemas[$uri] = Utils::loadJsonFromFile($this->uriToFile($uri));
        }

        return $this->schemas[$uri];
    }

    /**
     * Converts distant schema URI to a local one to load schemas from source code.
     *
     * @param string $uri
     *
     * @return string mixed
     */
    private function uriToFile($uri)
    {
        $uri = str_replace($this->baseUri, '', $uri);
        $schemaDir = realpath("{$this->kernelDir}/../vendor/json-quiz/json-quiz/format");

        return $schemaDir.'/'.$uri;
    }
}
