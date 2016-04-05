<?php

namespace HeVinci\CompetencyBundle\Form;

use HeVinci\CompetencyBundle\Form\Field\LevelsType;
use HeVinci\CompetencyBundle\Util\FormTestCase;
use Symfony\Component\Form\PreloadedExtension;

class ScaleTypeTest extends FormTestCase
{
    protected function getExtensions()
    {
        $levelType = new LevelsType();

        return [new PreloadedExtension([$levelType->getName() => $levelType], [])];
    }

    public function testSubmitValidData()
    {
        $formData = [
            'name' => 'Foo',
            'levels' => "A\nB"
        ];

        $form = $this->factory->create(new ScaleType());
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $scale = $form->getData();

        $this->assertInstanceOf('HeVinci\CompetencyBundle\Entity\Scale', $scale);
        $this->assertEquals('A', $scale->getLevels()[0]->getName());
        $this->assertEquals('B', $scale->getLevels()[1]->getName());
        $this->assertEquals(0, $scale->getLevels()[0]->getValue());
        $this->assertEquals(1, $scale->getLevels()[1]->getValue());
        $this->assertViewIsValid($form, $formData);
    }
}
