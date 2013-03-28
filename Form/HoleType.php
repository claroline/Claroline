<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class HoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'size', 'text'
            )
            //->add('position', 'text')
            ->add(
                'orthography', 'checkbox', array(
                    'required' => false, 'label' => 'Hole.orthography'
                )
            )
            ->add(
                'selector', 'checkbox', array(
                    'required' => false, 'label' => 'Hole.selector'
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
            )
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'UJM\ExoBundle\Entity\Hole'));
    }

    public function getName()
    {
        return 'ujm_exobundle_holetype';
    }
}
