<?php

namespace UJM\ExoBundle\Library\Validator;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Json\JsonSchema;
use UJM\ExoBundle\Library\Options\Validation;

/**
 * Base class for Validators that uses JSON schema definition.
 *
 * @DI\Service("ujm_exo.validator.json_schema", abstract=true)
 */
abstract class JsonSchemaValidator implements ValidatorInterface
{
    /**
     * @var JsonSchema
     */
    private $jsonSchema;

    /**
     * @param JsonSchema $jsonSchema
     *
     * @DI\InjectParams({
     *     "jsonSchema" = @DI\Inject("ujm_exo.library.json_schema")
     * })
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
        if (!in_array(Validation::NO_SCHEMA, $options)) {
            // Validate against JSON schema
            $errors = $this->validateSchema($data);
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
