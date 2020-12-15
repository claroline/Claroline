<?php

namespace UJM\ExoBundle\Library\Testing\Json;

use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * Base class for testing JSON Schema validators.
 */
abstract class JsonSchemaTestCase extends JsonDataTestCase
{
    /**
     * Creates a mock object for JsonSchema and injects it into the validator.
     *
     * @param JsonSchemaValidator $validator
     *
     * @return JsonSchemaValidator
     */
    protected function injectJsonSchemaMock(JsonSchemaValidator $validator)
    {
        $jsonSchema = $this->mock('UJM\ExoBundle\Library\Json\JsonSchema');
        $jsonSchema->expects($this->any())
            ->method('validate')
            ->willReturn([]);

        $validator->setJsonSchema($jsonSchema);

        return $validator;
    }

    /**
     * @param string $class
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mock($class)
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
