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
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'medium'
                )
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