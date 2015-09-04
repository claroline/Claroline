<?php

namespace HeVinci\CompetencyBundle\Form;

use Claroline\CoreBundle\Form\Field\TinymceType;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Form\ExerciseType;

class ExerciseTypeTest extends TypeTestCase
{
    protected function setUp()
    {
        $validator = $this->getMock('Symfony\Component\Validator\ValidatorInterface');
        $validator->expects($this->any())->method('validate')->willReturn([]);
        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addTypeExtension(new FormTypeValidatorExtension($validator))
            ->getFormFactory();
    }

    protected function getExtensions()
    {
        $tinyType = new TinymceType();

        return [new PreloadedExtension([$tinyType->getName() => $tinyType], [])];
    }

    public function testSubmitCreationFormWithValidData()
    {
        $formData = [
            'title' => 'Ex 1',
            'publish' => '1'
        ];

        $form = $this->factory->create(new ExerciseType(true));
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());

        $exercise = $form->getData();

        $this->assertInstanceOf('UJM\ExoBundle\Entity\Exercise', $exercise);
        $this->assertEquals('Ex 1', $exercise->getTitle());
        $this->assertEquals('1', $form->get('publish')->getData());

        $this->assertViewIsValid($form, $formData);
    }

    public function testSubmitEditionFormWithValidData()
    {
        $formData = [
            'title' => 'Ex 1',
            'description' => 'Desc...',
            'duration' => '3600',
            'dateCorrection' => '2012-01-12',
            'start_date' => '2012-01-10',
            'end_date' => '2012-01-11'
        ];

        $form = $this->factory->create(new ExerciseType(), new Exercise());
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());

        $exercise = $form->getData();

        $this->assertInstanceOf('UJM\ExoBundle\Entity\Exercise', $exercise);
        $this->assertEquals('Ex 1', $exercise->getTitle());
        $this->assertEquals('Desc...', $exercise->getDescription());
        $this->assertEquals('3600', $exercise->getDuration());

        $this->assertViewIsValid($form, $formData);
    }

    protected function assertViewIsValid(FormInterface $form, array $formData)
    {
        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
