<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ResourceNameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');
    }

    public function getName()
    {
        return 'resource_name_form';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'translation_domain' => 'platform'
        );
    }
}