<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CalendarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array('required' => true))
            ->add(
                'end',
                'date',
                array(
                    'format' => 'dd-MM-yyyy',
                    'widget' => 'choice',
                    'data' => new \DateTime('now')
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
                        '#FF0000' => 'High',
                        '#01A9DB' => 'Medium',
                        '#848484' => 'Low',
                    )
               // 'preferred_choices' => array('#01A9DB')
                )
            );
    }

    public function getName()
    {
        return 'calendar_form';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Claroline\CoreBundle\Entity\Event',
        );
    }

}