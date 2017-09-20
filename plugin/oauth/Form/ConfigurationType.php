<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\OAuthBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'client_id',
                'text',
                [
                    'constraints' => [
                        new NotBlank(),
                        new GreaterThanOrEqual(['value' => 0]),
                    ],
                    'attr' => ['min' => 0],
                    'label' => 'client_id',
                ]
            )
            ->add(
                'client_secret',
                'text',
                [
                    'constraints' => new NotBlank(),
                    'label' => 'client_secret',
                ]
            );
        if ($options['resource_owner'] !== 'linkedin') {
            $builder->add(
                'client_force_reauthenticate',
                'checkbox',
                [
                    'label' => 'client_force_reauthenticate',
                    'required' => false,
                ]
            );
        }
        if ($options['resource_owner'] === 'office_365') {
            $builder->add(
                'client_tenant_domain',
                'text',
                [
                    'required' => false,
                    'label' => 'client_tenant_domain',
                    'empty_data' => '',
                ]
            );
        }
        if ($options['resource_owner'] === 'generic') {
            $builder->add('access_token_url', 'text', [
                'required' => true,
                'label' => 'access_token_url',
            ])
            ->add('authorization_url', 'text', [
                'required' => true,
                'label' => 'authorization_url',
            ])
            ->add('infos_url', 'text', [
                'required' => true,
                'label' => 'infos_url',
            ])
            ->add('scope', 'text', [
                'required' => false,
                'label' => 'scope',
            ])
            ->add('paths_login', 'text', [
                'required' => false,
                'label' => 'paths_login',
            ])
            ->add('paths_email', 'text', [
                'required' => false,
                'label' => 'paths_email',
            ]);
        }

        $builder->add('client_active', 'checkbox', ['label' => 'client_active', 'required' => false]);
    }

    public function getName()
    {
        return 'platform_oauth_application_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'icap_oauth',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'resource_owner' => '',
        ]);
    }
}
