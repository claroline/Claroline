<?php

namespace Icap\WikiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name')->add('text');
    }

    public function getName()
    {
        return 'icap_section_form';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'translation_domain' => 'icap_section'
        );
    }
}