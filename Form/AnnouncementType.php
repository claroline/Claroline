<?php

namespace Claroline\AnnouncementBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AnnouncementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text', array('required' => false));
        $builder->add('announcer', 'text', array('required' => false));
        $builder->add(
            'content',
            'textarea',
            array(
                'required' => true,
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'medium'
                )
            )
        );
        $builder->add(
            'visible',
            'checkbox',
            array(
                'required' => false,
                'attr' => array('class' => 'visible-chk')
            )
        );

        $attr = array();
        $attr['class'] = 'datepicker input-small';
        $attr['data-date-format'] = 'dd-mm-yyyy';
        $attr['autocomplete'] = 'off';

        $builder->add(
            'visible_from',
            'date',
            array(
                'required' => false,
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => $attr,
                'input' => 'datetime'
            )
        );
        $builder->add(
            'visible_until',
            'date',
            array(
                'required' => false,
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => $attr,
                'input' => 'datetime'
            )
        );
    }

    public function getName()
    {
        return 'announcement_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'announcement'
            )
        );
    }
}