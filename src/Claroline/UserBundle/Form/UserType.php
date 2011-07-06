<?php

namespace Claroline\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('firstName', 'text')
                ->add('lastName', 'text')
                ->add('username', 'text')
                ->add('plainPassword', 'repeated', array('type' => 'password'));
    }
}