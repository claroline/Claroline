<?php

namespace HeVinci\CompetencyBundle\Form;

use HeVinci\CompetencyBundle\Util\FormTestCase;

class CompetencyTypeTest extends FormTestCase
{
    public function testSubmitValidData()
    {
        $formData = ['name' => 'Foo'];

        $form = $this->factory->create(new CompetencyType());
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $framework = $form->getData();

        $this->assertInstanceOf('HeVinci\CompetencyBundle\Entity\Competency', $framework);
        $this->assertEquals('Foo', $framework->getName());
        $this->assertViewIsValid($form, $formData);
    }
}
