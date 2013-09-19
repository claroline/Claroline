<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdminAnalyticsConnectionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'unique', 'buttongroupselect', array(
                    'label' => 'Show',
                    'attr' => array('class' => 'input-sm'),
                    'choices' => array(
                        'false' => 'connections',
                        'true' => 'unique connections'
                    )
                )
            )
            ->add(
                'range',
                'daterange',
                array(
                    'label' => 'for period',
                    'required' => false,
                    'attr' => array(
                        'class' => 'input-sm',
                        'style' => 'max-width:200px'
                    )
                )
            );
    }

    public function getName()
    {
        return 'admin_analytics_connections_form';
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
