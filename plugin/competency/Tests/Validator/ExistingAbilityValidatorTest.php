<?php

namespace HeVinci\CompetencyBundle\Tests\Validator;

use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Util\UnitTestCase;
use HeVinci\CompetencyBundle\Validator\ExistingAbility;
use HeVinci\CompetencyBundle\Validator\ExistingAbilityValidator;

class ExistingAbilityValidatorTest extends UnitTestCase
{
    private $context;
    private $repo;
    private $validator;

    protected function setUp()
    {
        $this->context = $this->mock('Symfony\Component\Validator\ExecutionContextInterface');
        $this->repo = $this->mock('HeVinci\CompetencyBundle\Repository\AbilityRepository');
        $om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $om->expects($this->any())
            ->method('getRepository')
            ->with('HeVinciCompetencyBundle:Ability')
            ->willReturn($this->repo);
        $this->validator = new ExistingAbilityValidator($om);
        $this->validator->initialize($this->context);
    }

    public function testValidateIgnoresEmptyValues()
    {
        $this->context->expects($this->never())->method('addViolation');
        $this->validator->validate('', new ExistingAbility());
    }

    public function testValidateIgnoresPreFetchedAbilities()
    {
        $this->context->expects($this->never())->method('addViolation');
        $this->validator->validate(new Ability(), new ExistingAbility());
    }

    public function testValidationFailure()
    {
        $constraint = new ExistingAbility();
        $this->repo->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'foo'])
            ->willReturn(null);
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($constraint->message);
        $this->validator->validate('foo', $constraint);
    }

    public function testValidate()
    {
        $this->repo->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'foo'])
            ->willReturn(new Ability());
        $this->context->expects($this->never())->method('addViolation');
        $this->validator->validate('foo', new ExistingAbility());
    }
}
