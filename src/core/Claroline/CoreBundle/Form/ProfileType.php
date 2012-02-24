<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\FormBuilder;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Form\UserType;

//TODO: phone verification

class ProfileType extends UserType
{
    private $grantRole;

    public function __construct($platformRoles)
    {
        foreach ($platformRoles as $role)
        {
            if ($role->getTranslationKey() == 'ROLE_ADMIN')
            {
                $this->grantRole = true;
            }
        }
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('administrativeCode', 'text', array('required' => false))
            ->add('plainPassword', 'repeated', array('type' => 'password'))
            ->add('mail', 'email', array('required' =>false))
            ->add('phone', 'text', array('required' => false));
        if ($this->grantRole == true)
        {
            $builder->add('ownedRoles', 'entity', array('class' => 'ClarolineCoreBundle:Role', 'expanded' => true, 'multiple' => true, 'property' => 'name', 'read_only' => false));
        }
        else
        {
            $builder->add('ownedRoles', 'entity', array('class' => 'ClarolineCoreBundle:Role', 'expanded' => true, 'multiple' => true, 'property' => 'name', 'read_only' => true));
        }
        $builder->add('note', 'textarea', array('required' => false));
    }

    public function getName()
    {
        return 'user_form';
    }

}

