<?php

namespace HeVinci\CompetencyBundle\Transfer;

use HeVinci\CompetencyBundle\Util\UnitTestCase;
use org\bovigo\vfs\vfsStream;

class ValidatorTest extends UnitTestCase
{
    private $jsonValidator;
    private $dataValidator;
    private $conflictValidator;
    private $validator;
    private $file;

    protected function setUp()
    {
        $this->jsonValidator = $this->mock('HeVinci\CompetencyBundle\Transfer\Validator\JsonValidator');
        $this->dataValidator = $this->mock('HeVinci\CompetencyBundle\Transfer\Validator\DataConstraintValidator');
        $this->conflictValidator = $this->mock('HeVinci\CompetencyBundle\Transfer\Validator\DataConflictValidator');
        $this->validator = new Validator($this->jsonValidator, $this->dataValidator, $this->conflictValidator);

        vfsStream::setup('root');
        $this->file = vfsStream::url('root/framework.json');
        file_put_contents($this->file, '');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testValidateThrowsIfNoFile()
    {
        $this->validator->validate('not-a-file');
    }

    public function testValidateMalformedJson()
    {
        file_put_contents($this->file, '<invalid-json>');
        $errors = $this->validator->validate($this->file);
        $this->assertEquals(Validator::ERR_TYPE_JSON, $errors['type']);
    }

    public function testValidateInvalidFramework()
    {
        file_put_contents($this->file, '{}');
        $this->jsonValidator->expects($this->once())
            ->method('validate')
            ->with(new \stdClass())
            ->willReturn([['message' => 'Error xyz', 'property' => 'a.b.c']]);
        $errors = $this->validator->validate($this->file);
        $this->assertEquals(Validator::ERR_TYPE_SCHEMA, $errors['type']);
        $this->assertEquals('Error xyz (path: a.b.c)', $errors['errors'][0]);
    }

    public function testValidateInconsistentFramework()
    {
        file_put_contents($this->file, '{}');
        $this->jsonValidator->expects($this->once())
            ->method('validate')
            ->with(new \stdClass())
            ->willReturn([]);
        $this->dataValidator->expects($this->once())
            ->method('validate')
            ->with(new \stdClass())
            ->willReturn(['Error xyz']);
        $errors = $this->validator->validate($this->file);
        $this->assertEquals(Validator::ERR_TYPE_INTERNAL, $errors['type']);
        $this->assertEquals('Error xyz', $errors['errors'][0]);
    }

    public function testValidateConflictualFramework()
    {
        file_put_contents($this->file, '{}');
        $this->jsonValidator->expects($this->once())
            ->method('validate')
            ->with(new \stdClass())
            ->willReturn([]);
        $this->dataValidator->expects($this->once())
            ->method('validate')
            ->with(new \stdClass())
            ->willReturn([]);
        $this->conflictValidator->expects($this->once())
            ->method('validate')
            ->with(new \stdClass())
            ->willReturn(['Error xyz']);
        $errors = $this->validator->validate($this->file);
        $this->assertEquals(Validator::ERR_TYPE_CONFLICT, $errors['type']);
        $this->assertEquals('Error xyz', $errors['errors'][0]);
    }

    public function testWithValidFramework()
    {
        file_put_contents($this->file, '{}');
        $this->jsonValidator->expects($this->once())
            ->method('validate')
            ->with(new \stdClass())
            ->willReturn([]);
        $this->dataValidator->expects($this->once())
            ->method('validate')
            ->with(new \stdClass())
            ->willReturn([]);
        $this->conflictValidator->expects($this->once())
            ->method('validate')
            ->with(new \stdClass())
            ->willReturn([]);
        $errors = $this->validator->validate($this->file);
        $this->assertEquals(Validator::ERR_TYPE_NONE, $errors['type']);
        $this->assertEquals([], $errors['errors']);
    }
}
