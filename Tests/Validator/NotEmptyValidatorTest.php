<?php

namespace HeVinci\CompetencyBundle\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use HeVinci\CompetencyBundle\Util\UnitTestCase;

class NotEmptyValidatorTest extends UnitTestCase
{
    private $context;
    private $validator;

    protected function setUp()
    {
        $this->context = $this->mock('Symfony\Component\Validator\ExecutionContextInterface');
        $this->validator = new NotEmptyValidator();
        $this->validator->initialize($this->context);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testValidateExpectsACollection()
    {
        $this->validator->validate('foo', new NotEmpty());
    }

    public function testValidateAddAViolationIfCollectionIsEmpty()
    {
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with((new NotEmpty())->message);
        $this->validator->validate(new ArrayCollection(), new NotEmpty());
    }
}
