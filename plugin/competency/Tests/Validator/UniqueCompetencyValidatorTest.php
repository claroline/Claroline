<?php

namespace HeVinci\CompetencyBundle\Validator;

use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Util\UnitTestCase;
use Symfony\Component\Validator\Constraints\NotBlank;

class UniqueCompetencyValidatorTest extends UnitTestCase
{
    private $repo;
    private $context;
    private $validator;

    protected function setUp()
    {
        $this->repo = $this->mock('HeVinci\CompetencyBundle\Repository\CompetencyRepository');
        $this->context = $this->mock('Symfony\Component\Validator\ExecutionContextInterface');
        $om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $om->expects($this->any())
            ->method('getRepository')
            ->with('HeVinciCompetencyBundle:Competency')
            ->willReturn($this->repo);
        $this->validator = new UniqueCompetencyValidator($om);
        $this->validator->initialize($this->context);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateExpectsUniqueCompetencyConstraint()
    {
        $this->validator->validate(new Competency(), new NotBlank());
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testValidateExpectsCompetencyInstance()
    {
        $this->validator->validate(new \stdClass(), new UniqueCompetency());
    }

    /**
     * @expectedException \LogicException
     */
    public function testValidateExpectsAParentCompetency()
    {
        $this->validator->validate(new Competency(), new UniqueCompetency());
    }

    public function testValidateWithExistingChildCompetency()
    {
        $parent = $this->mock('HeVinci\CompetencyBundle\Entity\Competency');
        $parent->expects($this->once())->method('getRoot')->willReturn(1);

        $competency = new Competency();
        $competency->setName('CHILD');
        $competency->setParent($parent);

        $this->repo->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'CHILD', 'root' => 1])
            ->willReturn(null);

        $this->context->expects($this->never())->method('addViolationAt');
        $this->validator->validate($competency, new UniqueCompetency());
    }

    public function testValidateWithNewChildCompetency()
    {
        $parent = $this->mock('HeVinci\CompetencyBundle\Entity\Competency');
        $parent->expects($this->once())->method('getRoot')->willReturn(1);

        $competency = new Competency();
        $competency->setName('CHILD');

        $constraint = new UniqueCompetency();
        $constraint->parentCompetency = $parent;

        $this->repo->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'CHILD', 'root' => 1])
            ->willReturn(null);

        $this->context->expects($this->never())->method('addViolationAt');
        $this->validator->validate($competency, $constraint);
    }

    public function testValidationFailure()
    {
        $parent = $this->mock('HeVinci\CompetencyBundle\Entity\Competency');
        $parent->expects($this->once())->method('getRoot')->willReturn(1);

        $competency = new Competency();
        $competency->setName('CHILD');

        $constraint = new UniqueCompetency();
        $constraint->parentCompetency = $parent;

        $this->repo->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'CHILD', 'root' => 1])
            ->willReturn('MATCH');

        $this->context->expects($this->once())
            ->method('addViolationAt')
            ->with('name', $constraint->message);
        $this->validator->validate($competency, $constraint);
    }
}
