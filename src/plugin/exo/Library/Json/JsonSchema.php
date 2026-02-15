<?php

namespace UJM\ExoBundle\Library\Json;

use JVal\Utils;
use JVal\Validator as SchemaValidator;

/**
 * Loads and validates JSON Schemas.
 */
class JsonSchema
{
    private string $baseUri = 'http://json-quiz.github.io/json-quiz/schemas';

    private string $projectDir;

    /**
     * List of loaded schemas.
     */
    private array $schemas = [];

    private ?SchemaValidator $validator = null;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * Validates data against the schema located at URI.
     *
     * @param mixed  $data - the data to validate
     * @param string $uri  - the URI of the schema to use
     */
    public function validate(mixed $data, string $uri): array
    {
        return $this->getValidator()->validate($data, $this->getSchema($uri));
    }

    /**
     * Get schema validator.
     */
    private function getValidator(): SchemaValidator
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
     */
    private function getSchema(string $uri): object
    {
        if (empty($this->schemas[$uri])) {
            $this->schemas[$uri] = Utils::loadJsonFromFile($this->uriToFile($uri));
        }

        return $this->schemas[$uri];
    }

    /**
     * Converts distant schema URI to a local one to load schemas from source code.
     */
    private function uriToFile(string $uri): string
    {
        $uri = str_replace($this->baseUri, '', $uri);
        $schemaDir = realpath("{$this->projectDir}/src/plugin/exo/Resources/schemas");

        return $schemaDir.'/'.$uri;
    }
}
