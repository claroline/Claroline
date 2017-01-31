<?php

namespace UJM\ExoBundle\Tests\Library\Validator;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use UJM\ExoBundle\Library\Json\JsonSchema;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

class JsonSchemaValidatorTest extends TransactionalTestCase
{
    /**
     * @var JsonSchemaValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var JsonSchema|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonSchema;

    public function setUp()
    {
        parent::setUp();

        $this->validator = $this->getMockForAbstractClass('UJM\ExoBundle\Library\Validator\JsonSchemaValidator');

        $this->jsonSchema = $this->getMock('UJM\ExoBundle\Library\Json\JsonSchema', [], [], '', false);
        $this->validator->setJsonSchema($this->jsonSchema);
    }

    /**
     * The Validator MUST perform additional validations if the Schema does not throw errors.
     */
    public function testValidatePerformsValidateAfterSchemaIfNoError()
    {
        // Validate schema generates no error
        $this->jsonSchema->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        // If no errors on schema validation, custom validation is performed
        $this->validator->expects($this->once())
            ->method('validateAfterSchema');

        $this->validator->validate([]);
    }

    /**
     * The Validator MUST NOT perform additional validations if the Schema throws errors.
     */
    public function testValidateNotPerformValidateAfterSchemaIfErrors()
    {
        // Validate schema generates errors
        $this->jsonSchema->expects($this->once())
            ->method('validate')
            ->willReturn(['some errors']);

        // If errors on schema validation, no custom validation is performed
        $this->validator->expects($this->never())
            ->method('validateAfterSchema');

        $this->validator->validate([]);
    }

    /**
     * The validator MUST NOT perform Schema validation if the `NO_SCHEMA` option is set.
     */
    public function testNoSchemaValidationIfOptionSet()
    {
        // Validate schema generates errors
        $this->jsonSchema->expects($this->never())
            ->method('validate');

        $this->validator->validate([], [Validation::NO_SCHEMA]);
    }
}
