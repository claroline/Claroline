<?php

namespace HeVinci\CompetencyBundle\Form;

use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Entity\Scale;
use HeVinci\CompetencyBundle\Util\FormTestCase;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\PreloadedExtension;

class AbilityTypeTest extends FormTestCase
{
    protected function getExtensions()
    {
        $registry = $this->client->getContainer()->get('doctrine');
        $entityType = new EntityType($registry);

        return [new PreloadedExtension([$entityType->getName() => $entityType], [])];
    }

    /**
     * @expectedException \LogicException
     */
    public function testCompetencyOptionIsRequired()
    {
        $this->factory->create(new AbilityType());
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
        $om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $om->persist($scale);
        $om->persist($level);
        $om->persist($parent);
        $om->flush();

        $formData = [
            'name' => 'Foo',
            'level' => $level->getId(),
            'minActivityCount' => 2,
        ];

        $form = $this->factory->create(new AbilityType(), null, ['competency' => $parent]);
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $ability = $form->getData();

        $this->assertInstanceOf('HeVinci\CompetencyBundle\Entity\Ability', $ability);
        $this->assertEquals('Foo', $ability->getName());
        $this->assertEquals($level, $ability->getLevel());
        $this->assertEquals(2, $ability->getMinActivityCount());
        $this->assertViewIsValid($form, $formData);
    }
}
