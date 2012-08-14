<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

//TODO: phone verification

class ProfileType extends BaseProfileType
{
    private $grantRole;

    public function __construct($platformRoles)
    {
        foreach ($platformRoles as $role) {
            if ($role->getTranslationKey() == 'ROLE_ADMIN') {
                $this->grantRole = true;
            }
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('administrativeCode', 'text', array('required' => false))
            ->add('plainPassword', 'repeated', array('type' => 'password'))
            ->add('mail', 'email', array('required' => false))
            ->add('phone', 'text', array('required' => false));
        if ($this->grantRole == true) {
            $builder->add(
                'ownedRoles',
                'entity',
                array(
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'expanded' => false,
                    'multiple' => true,
                    'property' => 'name',
                    'disabled' => false
                )
            );
        } else {
            $builder->add(
                'ownedRoles',
                'entity',
                array(
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'expanded' => false,
                    'multiple' => true,
                    'property' => 'name',
                    'disabled' => true
                )
            );
        }
        $builder->add('note', 'textarea', array('required' => false));
    }

    public function getName()
    {
        return 'profile_form';
    }
}