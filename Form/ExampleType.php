<?php

namespace Claroline\ExampleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ExampleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //The field name is mandatory. This is a non mapped variable of AbstractResource.
        $builder->add('name', 'text');
        $builder->add('text', 'textarea');
    }

    public function getName()
    {
        return 'example_form';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'translation_domain' => 'resource'
        );
    }
}