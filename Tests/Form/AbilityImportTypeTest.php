<?php

namespace HeVinci\CompetencyBundle\Form;

use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Entity\Scale;
use HeVinci\CompetencyBundle\Util\FormTestCase;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;

class AbilityImportTypeTest extends FormTestCase
{
    private $om;

    protected function setUp()
    {
        parent::setUp();
        $this->om = $om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $validator = $this->getMock('Symfony\Component\Validator\ValidatorInterface');
        $validator->expects($this->any())->method('validate')->willReturn([]);
        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addTypeExtension(new FormTypeValidatorExtension($validator))
            ->getFormFactory();
    }

    /**
     * @expectedException \LogicException
     */
    public function testCompetencyOptionIsRequired()
    {
        $this->factory->create(new AbilityImportType($this->om));
    }

    protected function getExtensions()
    {
        $registry = $this->client->getContainer()->get('doctrine');
        $entityType = new EntityType($registry);

        return [new PreloadedExtension([$entityType->getName() => $entityType], [])];
    }

    public function testSubmitValidData()
    {
        $scale = new Scale();
        $scale->setName('Scale 1');
        $level = new Level();
        $level->setName('Level 1');
        $level->setValue(0);
        $level->setScale($scale);
        $parent = new Competency();
        $parent->setName('Competency 1');
        $parent->setScale($scale);
        $ability = new Ability();
        $ability->setName('Foo');
        $this->om->persist($scale);
        $this->om->persist($level);
        $this->om->persist($parent);
        $this->om->persist($ability);
        $this->om->flush();

        $formData = [
            'ability' => 'Foo',
            'level' => $level->getId()
        ];

        $form = $this->factory->create(new AbilityImportType($this->om), null, ['competency' => $parent]);
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($ability, $form->getData());
        $this->assertEquals($level, $ability->getLevel());
        $this->assertViewIsValid($form, $formData);
    }
}
