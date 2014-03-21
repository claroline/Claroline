<?php

namespace Claroline\CoreBundle\Form;

use Claroline\CoreBundle\Entity\UserPublicProfilePreferences;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserPublicProfilePreferencesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('share_policy', 'choice', array(
                'choices'  => UserPublicProfilePreferences::getSharePolicies(),
                'required' => true,
                'expanded' => true,
                'attr'     => array(
                    'class' => 'share_policies'
                )
            ))
            ->add('display_base_informations', 'checkbox' , array(
                'required' => false,
                'mapped'   => false
            ))
            ->add('display_phone_number', 'checkbox', array(
                'required' => false
            ))
            ->add('display_email', 'checkbox', array(
                'required' => false
            ))
        ;
    }

    public function getName()
    {
        return 'user_public_profile_preferences_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
           'translation_domain' => 'platform',
            'data_class'        => 'Claroline\CoreBundle\Entity\UserPublicProfilePreferences',
            'csrf_protection'   => true,
            'intention'         => 'configure_public_profile_preferences'
        ));
    }
}