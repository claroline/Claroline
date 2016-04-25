<?php

namespace HeVinci\CompetencyBundle\Validator;

use HeVinci\CompetencyBundle\Transfer\Validator;
use HeVinci\CompetencyBundle\Util\UnitTestCase;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\NotBlank;

class ImportableFrameworkValidatorTest extends UnitTestCase
{
    private $context;
    private $manager;
    private $fakeFile;
    private $validator;

    protected function setUp()
    {
        $this->context = $this->mock('Symfony\Component\Validator\Context\ExecutionContextInterface');
        $this->manager = $this->mock('HeVinci\CompetencyBundle\Transfer\Validator');
        $this->validator = new ImportableFrameworkValidator($this->manager);
        $this->validator->initialize($this->context);

        vfsStream::setup('root');
        $path = vfsStream::url('root/framework.json');
        file_put_contents($path, '{}');

        $this->fakeFile = new UploadedFile($path, 'framework.json');
    }

    public function testValidatorIgnoresNonFileValue()
    {
        $this->manager->expects($this->never())->method('validate');
        $this->validator->validate('not-a-file', new ImportableFramework());
    }

    public function testValidatorIgnoresOtherConstraints()
    {
        $this->manager->expects($this->never())->method('validate');
        $this->validator->validate($this->fakeFile, new NotBlank());
    }

    /**
     * @dataProvider managerValidationProvider
     *
     * @param string $errorType
     * @param array  $errors
     */
    public function testValidate($errorType, array $errors)
    {
        $this->manager->expects($this->once())
            ->method('validate')
            ->with('{}')
            ->willReturn([
                'type' => $errorType,
                'errors' => $errors,
            ]);
        $builder = $this->mock('Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface');
        $builder->expects($this->any())
            ->method('setParameter')
            ->willReturn($builder);
        // no error == no violation; x errors == 1 violation for type description + x violations for each message
        $expectedViolationsCount = count($errors) ? count($errors) + 1 : 0;
        $this->context->expects($this->exactly($expectedViolationsCount))
            ->method('buildViolation')
            ->willReturn($builder);

        $this->validator->validate($this->fakeFile, new ImportableFramework());
    }

    public function managerValidationProvider()
    {
        return [
            [Validator::ERR_TYPE_NONE, []],
            [Validator::ERR_TYPE_JSON, ['a', 'b']],
            [Validator::ERR_TYPE_SCHEMA, ['a', 'b']],
            [Validator::ERR_TYPE_INTERNAL, ['a', 'b']],
            [Validator::ERR_TYPE_CONFLICT, ['a', 'b', 'c']],
        ];
    }
}
