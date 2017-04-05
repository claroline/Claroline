<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CasBundle\Form;

use Claroline\CasBundle\Library\Configuration\CasServerConfiguration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CasServerConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                [
                    'constraints' => new NotBlank(),
                    'label' => 'cas_login_name',
                ]
            )
            ->add(
                'login_url',
                'url',
                [
                    'constraints' => new NotBlank(),
                    'label' => 'cas_login_url',
                ]
            )
            ->add(
                'logout_url',
                'url',
                [
                    'constraints' => new NotBlank(),
                    'label' => 'cas_logout_url',
                ]
            )
            ->add(
                'validation_url',
                'url',
                [
                    'constraints' => new NotBlank(),
                    'label' => 'cas_validation_url',
                ]
            )
            ->add(
                'active',
                'checkbox',
                [
                    'label' => 'cas_active',
                    'required' => false, ]
            )
            ->add(
                'login_option',
                'choice',
                [
                    'label' => 'cas_login_option',
                    'required' => true,
                    'choices' => [
                        'cas_default_login' => CasServerConfiguration::DEFAULT_LOGIN,
                        'cas_primary_login' => CasServerConfiguration::PRIMARY_LOGIN,
                    ],
                    'choices_as_values' => true,
                    'expanded' => true,
                    'multiple' => false,
                    'attr' => ['class' => 'cas-login-option-list'],
                ]
            );
    }

    public function getName()
    {
        return 'claroline_cas_server_configuration_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'claroline_cas',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
        ]);
    }
}
