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
        $builder
            ->add('title', 'text', array('required' => true))
            ->add(
                'start',
                'date',
                array(
                    'format' => 'd-M-yyyy H:mm',
                    'widget' => 'single_text',
                )
            )
            ->add(
                'end',
                'date',
                array(
                    'format' => 'd-M-yyyy H:mm',
                    'widget' => 'single_text',
                )
            )
            ->add(
                'allDay',
                'checkbox'
            )
            ->add(
                'description',
                'textarea',
                array(
                    'attr' => array(
                        'class' => 'tinymce',
                        'data-theme' => 'simple'
                    )
                )
            )
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
