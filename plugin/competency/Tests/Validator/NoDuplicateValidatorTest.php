<?php

namespace HeVinci\CompetencyBundle\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Entity\Scale;
use HeVinci\CompetencyBundle\Util\UnitTestCase;

class NoDuplicateValidatorTest extends UnitTestCase
{
    private $context;
    private $validator;

    protected function setUp()
    {
        $this->context = $this->mock('Symfony\Component\Validator\ExecutionContextInterface');
        $this->validator = new NoDuplicateValidator();
        $this->validator->initialize($this->context);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateExpectsAnArrayOrATraversableInstance()
    {
        $this->validator->validate('foo', new NoDuplicate());
    }

    public function testValidateAddAViolationIfArrayHasDuplicates()
    {
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with((new NoDuplicate())->message);
        $this->validator->validate(['foo', 'bar', 'foo'], new NoDuplicate());
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
     */
    public function testValidateWithPropertyExpectsElementsToBeObjects()
    {
        $constraint = new NoDuplicate();
        $constraint->property = 'name';
        $this->validator->validate([new Scale(), 'foo'], $constraint);
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\AccessException
     */
    public function testValidateWithPropertyExpectsThePropertyToBeAccessibleOnEachElement()
    {
        $constraint = new NoDuplicate();
        $constraint->property = 'name';
        $this->validator->validate([new Scale(), new \stdClass()], $constraint);
    }

    /**
     * @dataProvider validValueProvider
     *
     * @param mixed       $value
     * @param NoDuplicate $constraint
     */
    public function testValidate($value, NoDuplicate $constraint)
    {
        $this->context->expects($this->never())->method('addViolation');
        $this->validator->validate($value, $constraint);
    }

    public function validValueProvider()
    {
        $levelOne = new Level();
        $levelOne->setName('A');
        $levelTwo = new Level();
        $levelTwo->setName('B');
        $levelConstraint = new NoDuplicate();
        $levelConstraint->property = 'name';

        return [
            [['foo', 'bar', 'baz'], new NoDuplicate()],
            [new ArrayCollection([$levelOne, $levelTwo]), $levelConstraint],
        ];
    }
}
