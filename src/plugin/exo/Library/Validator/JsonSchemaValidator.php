<?php

namespace UJM\ExoBundle\Library\Validator;

use UJM\ExoBundle\Library\Json\JsonSchema;
use UJM\ExoBundle\Library\Options\Validation;

/**
 * Base class for Validators that uses JSON schema definition.
 */
abstract class JsonSchemaValidator implements ValidatorInterface
{
    /**
     * @var JsonSchema
     */
    private $jsonSchema;

    /**
     * @param JsonSchema $jsonSchema
     */
    public function setJsonSchema($jsonSchema)
    {
        $this->jsonSchema = $jsonSchema;
    }

    /**
     * URI to the JSON Schema to use for validation.
     *
     * @return string
     */
    abstract public function getJsonSchemaUri();

    /**
     * Performs any validation task that cannot be achieved using solely the
     * JSON Schema validator. Returns an array of validation errors, if any.
     *
     * @param mixed $data    - the data to validate
     * @param array $options - the validation options
     *
     * @return array
     */
    abstract public function validateAfterSchema($data, array $options = []);

    /**
     * Validates the data against the validator schema
     * and custom rules (defined in method `validateAfterSchema`).
     *
     * @param mixed $data    - the data to validate
     * @param array $options - the validation options
     *
     * @return array - the list of validation errors
     */
    public function validate($data, array $options = [])
    {
        // convert data arrays to stdClass for JsonSchema validator
        $dataObject = json_decode(json_encode($data));
        if ($dataObject === []) {
            $dataObject = new \stdClass();
        }

        if (!in_array(Validation::NO_SCHEMA, $options)) {
            // Validate against JSON schema
            $errors = $this->validateSchema($dataObject);
        }

        if (empty($errors)) {
            // Perform additional checks
            $errors = $this->validateAfterSchema($data, $options);
        }

        return $errors;
    }

    /**
     * Validates data against JSON schema.
     *
     * @param mixed $data - the data to validate
     *
     * @return array - the list of validation errors
     */
    private function validateSchema($data)
    {
        return $this->jsonSchema->validate($data, $this->getJsonSchemaUri());
    }
}
