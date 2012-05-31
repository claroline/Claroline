<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class LinkType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('url', 'text');
        $builder->add('name', 'text');
    }

    public function getName()
    {
        return 'link_form';
    }
}