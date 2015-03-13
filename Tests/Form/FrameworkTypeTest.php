<?php

namespace HeVinci\CompetencyBundle\Form;

use HeVinci\CompetencyBundle\Entity\Scale;
use HeVinci\CompetencyBundle\Util\FormTestCase;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;

class FrameworkTypeTest extends FormTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $validator = $this->getMock('Symfony\Component\Validator\ValidatorInterface');
        $validator->expects($this->any())->method('validate')->willReturn([]);
        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addTypeExtension(new FormTypeValidatorExtension($validator))
            ->getFormFactory();
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
        $om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $om->persist($scale);
        $om->flush();

        $formData = [
            'name' => 'Foo',
            'description' => 'Foo...',
            'scale' => $scale->getId()
        ];

        $form = $this->factory->create(new FrameworkType());
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $framework = $form->getData();

        $this->assertInstanceOf('HeVinci\CompetencyBundle\Entity\Competency', $framework);
        $this->assertEquals('Foo', $framework->getName());
        $this->assertEquals('Foo...', $framework->getDescription());
        $this->assertEquals($scale, $framework->getScale());
        $this->assertViewIsValid($form, $formData);
    }
}
