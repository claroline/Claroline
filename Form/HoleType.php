<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'position', 'text'
            )
            ->add(
                'size', 'text', array(
                'attr'  => array( 'size' => '4')
                )

            )
            //->add('position', 'text')
            ->add(
                'orthography', 'checkbox', array(
                    'required' => false, 'label' => 'orthography'
                )
            )
            ->add(
                'selector', 'checkbox', array(
                    'required' => false, 'label' => 'hole_selector'
                )
            )
            ->add(
                'wordResponses', 'collection', array(
                    'type' => new WordResponseType,
                    'prototype' => true,
                    //'by_reference' => false,
                    'allow_add' => true,
                    'allow_delete' => true
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
                array('data_class' => 'UJM\ExoBundle\Entity\Hole',
                      'cascade_validation' => true,
                      'translation_domain' => 'ujm_exo'
                )
        );
    }

    public function getName()
    {
        return 'ujm_exobundle_holetype';
    }
}
