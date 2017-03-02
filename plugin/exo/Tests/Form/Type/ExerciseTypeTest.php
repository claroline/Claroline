<?php

namespace UJM\ExoBundle\Tests\Form\Type;

use Claroline\CoreBundle\Form\Field\TinymceType;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use UJM\ExoBundle\Form\Type\ExerciseType;

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
            'name' => 'Ex 1',
            'published' => '1',
        ];

        $form = $this->factory->create(new ExerciseType());
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());

        $exercise = $form->getData();

        $this->assertInstanceOf('UJM\ExoBundle\Entity\Exercise', $exercise);
        $this->assertEquals('Ex 1', $exercise->getName());
        $this->assertEquals('1', $form->get('published')->getData());

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
