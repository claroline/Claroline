<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdminAnalyticsTopType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'top_type', 'choice', array(
                    'label' => 'Show',
                    'attr' => array(
                        'class' => 'input-medium',
                        'style' => 'max-width:200px;'
                    ),
                    'choices' => array(
                        'top_extension' => 'top_extension',
                        'top_workspaces_resources' => 'top_workspaces_resources',
                        'top_workspaces_connections' => 'top_workspaces_connections',
                        'top_resources_views' => 'top_resources_views',
                        'top_resources_downloads' => 'top_resources_downloads',
                        'top_users_workspaces_owners' => 'top_users_workspaces_owners',
                        'top_users_workspaces_enrolled' => 'top_users_workspaces_enrolled',
                        'top_users_connections' => 'top_users_connections',
                        'top_media_views' => 'top_media_views'
                    )
                )
            )
            ->add(
                'range',
                'daterange',
                array(
                    'label' => 'for period',
                    'required' => false,
                    'attr' => array('class' => 'input-medium')
                )
            )
            ->add(
                'top_number', 'buttongroupselect', array(
                    'label' => 'top',
                    'attr' => array('class' => 'input-medium'),
                    'choices' => array(
                        '20' => '20',
                        '30' => '30',
                        '50' => '50',
                        '100' => '100'
                    )
                )
            );
    }

    public function getName()
    {
        return 'admin_analytics_top_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform',
                'csrf_protection'   => false
            )
        );
    }
}
