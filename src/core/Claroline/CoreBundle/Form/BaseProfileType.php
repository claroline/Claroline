<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class BaseProfileType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('firstName', 'text')
            ->add('lastName', 'text')
            ->add('username', 'text')
            ->add('plainPassword', 'repeated', array('type' => 'password'));
    }

    public function getName()
    {
        return 'profile_form';
    }
}