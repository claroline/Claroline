<?php

namespace Claroline\HTMLPageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class HTMLPageType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('archive', 'file');
        $builder->add('index_page', 'string');
    }

    public function getName()
    {
        return 'html_page_form';
    }
}