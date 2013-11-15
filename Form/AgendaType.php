<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AgendaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $recurring = array();

        for ($i = 0; $i < 10 ; $i++) { 
            $recurring[$i] = $i; 
        }
        $attr = array();
        $attr['class'] = 'datepicker input-small';
        $attr['data-date-format'] = 'dd-mm-yyyy';
        $attr['autocomplete'] = 'off';
        $builder
            ->add('title', 'text', array('required' => true))
            ->add(
                'start',
                'datepicker',
                array(
                    'required'      => false,
                    'widget'        => 'single_text',
                    'format'        => 'dd-MM-yyyy HH:mm',
                    'attr'          => $attr,
               )
            )
            ->add(
                'startHours',
                'text',
                array(
                    'attr' => array('class' => 'hours')
                    )
                )
            ->add(
                'end',
                'datepicker',
                array(
                    'required'      => false,
                    'widget'        => 'single_text',
                    'format'        => 'dd-MM-yyyy HH:mm',
                    'attr'          => $attr,
               )
            )
            ->add(
                'endHours',
                'text',
                array(
                    'attr' => array('class' => 'hours')
                    )
                )
            ->add('allDay','checkbox')
            ->add('description','tinymce')
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
            )
            ->add(
                'recurring',
                'choice',
                array('choices' => $recurring)
            );
    }

    public function getName()
    {
        return 'agenda_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'title' => 'hello',
                'priority' => '#FF0000',
                'workspace' => new SimpleWorkspace() ,
                'user' => new \Claroline\CoreBundle\Entity\User(),
                'class' => 'Claroline\CoreBundle\Entity\Event',
                'translation_domain' => 'agenda'
            )
        )
        ->setRequired(
            array(
                'title',
                'priority',
                'workspace',
                'user'
            )
        )
        ->setOptional(
            array(
                'start',
                'end'
            )
        )
        ->setAllowedtypes(
            array(
                'title' => 'string',
                'workspace' => 'Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace',
                'user' => 'Claroline\CoreBundle\Entity\User'
            )
        )
        ->setAllowedValues(
            array(
                'priority' => array('#FF0000', '#01A9DB', '#848484')
            )
        );
    }
}
