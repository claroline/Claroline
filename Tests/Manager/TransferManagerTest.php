<?php

namespace HeVinci\CompetencyBundle\Manager;

use HeVinci\CompetencyBundle\Util\UnitTestCase;
use org\bovigo\vfs\vfsStream;

class TransferManagerTest extends UnitTestCase
{
    private $jsonValidator;
    private $dataValidator;
    private $conflictValidator;
    private $manager;
    private $file;

    protected function setUp()
    {
        $this->jsonValidator = $this->mock('HeVinci\CompetencyBundle\Transfer\JsonValidator');
        $this->dataValidator = $this->mock('HeVinci\CompetencyBundle\Transfer\DataConstraintValidator');
        $this->conflictValidator = $this->mock('HeVinci\CompetencyBundle\Transfer\DataConflictValidator');
        $this->manager = new TransferManager($this->jsonValidator, $this->dataValidator, $this->conflictValidator);

        vfsStream::setup('root');
        $this->file = vfsStream::url('root/framework.json');
        file_put_contents($this->file, '');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testValidateThrowsIfNoFile()
    {
        $this->manager->validate('not-a-file');
    }

    public function testValidateMalformedJson()
    {
        file_put_contents($this->file, '<invalid-json>');
        $errors = $this->manager->validate($this->file);
        $this->assertEquals(TransferManager::ERR_TYPE_JSON, $errors['type']);
    }

    public function testValidateInvalidFramework()
    {
        file_put_contents($this->file, '{}');
        $this->jsonValidator->expects($this->once())
            ->method('validate')
            ->with(new \stdClass())
            ->willReturn([['message' => 'Error xyz', 'property' => 'a.b.c']]);
        $errors = $this->manager->validate($this->file);
        $this->assertEquals(TransferManager::ERR_TYPE_SCHEMA, $errors['type']);
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
        $errors = $this->manager->validate($this->file);
        $this->assertEquals(TransferManager::ERR_TYPE_INTERNAL, $errors['type']);
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
        $errors = $this->manager->validate($this->file);
        $this->assertEquals(TransferManager::ERR_TYPE_CONFLICT, $errors['type']);
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
        $errors = $this->manager->validate($this->file);
        $this->assertEquals(TransferManager::ERR_TYPE_NONE, $errors['type']);
        $this->assertEquals([], $errors['errors']);
    }
}
