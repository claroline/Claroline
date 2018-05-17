<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminAnalyticsTopType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'top_type', ChoiceType::class, [
                    'label' => 'show',
                    'attr' => [
                        'class' => 'input-sm',
                    ],
                    'choices' => [
                        'top_extension' => 'top_extension',
                        'top_workspaces_resources' => 'top_workspaces_resources',
                        'top_workspaces_connections' => 'top_workspaces_connections',
                        'top_resources_views' => 'top_resources_views',
                        'top_resources_downloads' => 'top_resources_downloads',
                        'top_users_workspaces_owners' => 'top_users_workspaces_owners',
                        'top_users_workspaces_enrolled' => 'top_users_workspaces_enrolled',
                        'top_users_connections' => 'top_users_connections',
                        'top_media_views' => 'top_media_views',
                    ],
                    'theme_options' => ['label_width' => 'col-md-2', 'control_width' => 'col-md-4'],
                ]
            )
            ->add(
                'range',
                'daterange',
                [
                    'label' => 'for_period',
                    'required' => false,
                    'attr' => ['class' => 'input-sm'],
                    'theme_options' => ['label_width' => 'col-md-2', 'control_width' => 'col-md-4'],
                ]
            )
            ->add(
                'top_number', 'buttongroupselect', [
                    'label' => 'top',
                    'attr' => ['class' => 'input-sm'],
                    'choices' => [
                        '20' => '20',
                        '30' => '30',
                        '50' => '50',
                        '100' => '100',
                    ],
                    'theme_options' => ['label_width' => 'col-md-2', 'control_width' => 'col-md-3'],
                ]
            );
    }

    public function getName()
    {
        return 'admin_analytics_top_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
        ->setDefaults(
            [
                'translation_domain' => 'platform',
                'csrf_protection' => false,
            ]
        );
    }
}
