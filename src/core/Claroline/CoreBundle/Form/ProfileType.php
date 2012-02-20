<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ProfileType extends AbstractType
{/*
    public function __construct ($id)
    {
        
    }*/

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('id', 'hidden')
            ->add('firstName', 'text')
            ->add('lastName', 'text')
            ->add('note', 'textarea', array('required' => false))
            ->add('mail', 'email')         
            ->add('phone' ,'text', array('required' => false))
            ->add('plainPassword', 'repeated', array('type' => 'password'));
    }

    public function getName()
    {
        return 'user_form';
    }

}

