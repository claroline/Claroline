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
