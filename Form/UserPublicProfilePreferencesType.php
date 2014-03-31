<?php

namespace Claroline\CoreBundle\Form;

use Claroline\CoreBundle\Entity\UserPublicProfilePreferences;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
            ->add('display_email', 'checkbox', array(
                'required' => false
            ))
            ->add('display_phone_number', 'checkbox', array(
                'required' => false
            ))
            ->add('allow_mail_sending', 'checkbox', array(
                'required' => false
            ))
            ->add('allow_message_sending', 'checkbox', array(
                'required' => false
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            /** @var \Claroline\CoreBundle\Entity\UserPublicProfilePreferences $userPublicProfilePreferences */
            $userPublicProfilePreferences = $event->getData();

            if (null !== $userPublicProfilePreferences) {
                $form                      = $event->getForm();
                $baseInformationsIsChecked  = false;
                $baseInformationsIsDisabled = false;

                if (0 < $userPublicProfilePreferences->getSharePolicy()) {
                    $baseInformationsIsChecked  = 'checked';
                    $baseInformationsIsDisabled = 'disabled';
                }

                $form
                    ->add('display_base_informations', 'checkbox' , array(
                        'required' => false,
                        'mapped'   => false,
                        'attr'     => array(
                            'checked'  => $baseInformationsIsChecked,
                            'disabled' => $baseInformationsIsDisabled
                        )
                    ));
            }

        });
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