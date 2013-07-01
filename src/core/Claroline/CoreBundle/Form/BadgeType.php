<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BadgeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('description', 'text')
            ->add('criteria', 'textarea', array(
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'medium'
                    )
                )
            )
            ->add('version', 'integer')
            ->add('file', 'file', array(
                    'label' => 'badge_form_image'
                ))
            ->add('expired_at', 'datepicker', array(
                  'read_only' => true,
                  'component' => true,
                  'autoclose' => true
            ))
        ;
    }

    public function getName()
    {
        return 'badge_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform'
                )
        );
    }
}
