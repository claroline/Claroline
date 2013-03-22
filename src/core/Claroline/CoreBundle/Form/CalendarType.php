<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CalendarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array('required' => true))
            ->add(
                'start',
                'date',
                array(
                    'format' => 'd-M-yyyy',
                    'widget' => 'single_text',
                    )
            )
            ->add(
                'end',
                'date',
                array(
                    'format' => 'd-M-yyyy',
                    'widget' => 'single_text',
                )
            )
            ->add(
                'allDay',
                'checkbox',
                array(
                'label' => 'all day ?',
                )
            )
            ->add('description', 'textarea')
            ->add(
                'priority',
                'choice',
                array(
                    'choices' => array(
                        '#FF0000' => 'high',
                        '#01A9DB' => 'medium',
                        '#848484' => 'low',
                    )
                )
            );
    }

    public function getName()
    {
        return 'calendar_form';
    }


    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'class' => 'Claroline\CoreBundle\Entity\Event',
                'translation_domain' => 'calendar'
                )
        )

         ->setOptional(
             array(
                'start',
                'end'
            )
        ->setAllowedTypes()
         );
    }
}