<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HintType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'value', 'textarea', array(
                    'label' => 'hint',
                    'attr' => array('style' => 'height:57px',
                                      'class'=> 'form-control')
                )
            )
            ->add(
                'penalty', 'text', array(
                    'label' => 'penalty',
                    'attr' => array('style' => 'width:50px; text-align: end;')
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'UJM\ExoBundle\Entity\Hint',
            )
        );
    }

    public function getName()
    {
        return 'ujm_exobundle_hinttype';
    }

}
