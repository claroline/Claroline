<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LogWorkspaceWidgetConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('creation', 'checkbox', array('required' => false))
            ->add('read', 'checkbox', array('required' => false))
            ->add('export', 'checkbox', array('required' => false))
            ->add('update', 'checkbox', array('required' => false))
            ->add('updateChild', 'checkbox', array('required' => false))
            ->add('delete', 'checkbox', array('required' => false))
            ->add('move', 'checkbox', array('required' => false))
            ->add('subscribe', 'checkbox', array('required' => false))
            ->add('amount', 'choice', array(
                'choices' => array(
                    '1' => '1',
                    '5' => '5',
                    '10' => '10',
                    '15' => '15',
                    '20' => '20'
                ),
                'required' => true
            ));
    }

    public function getName()
    {
        return 'log_widget_config';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'platform'
        ));
    }
}
