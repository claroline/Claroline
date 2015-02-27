<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'ordre', 'text'
            )
            ->add(
                'rightResponse', 'checkbox', array(
                    'required' => false, 'label' => ' '
                )
            )
            ->add(
                'label', 'tinymce', array(
                     'attr' => array('data-new-tab' => 'yes'),
                    'label' => ' '
                )
            )
            ->add(
                'weight', 'text', array(
                    'required' => false, 'label' => ' ', 'attr' => array('style' => 'width:50px; text-align: end;')
                )
            )
            ->add(
                'feedback', 'tinymce', array(
                    'attr' => array('data-new-tab' => 'yes'),
                    'required' => false, 'label' => ' '
                )
            )
            ->add(
                'positionForce', 'checkbox', array(
                    'required' => false, 'label' => ' '
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'UJM\ExoBundle\Entity\Choice',
            )
        );
    }

    public function getName()
    {
        return 'ujm_exobundle_choicetype';
    }

}