<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LabelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'value', 'tinymce', array(
                    'required' => false, 'label' => ' '
                ))
            ->add(
                'scoreRight', 'text', array(
                    'required' => false, 'label' => ' ', 'attr' => array('style' => 'width:35px; text-align: end;'
                  )))
            ->add(
                'correspondence', 'choice', array(
                    'choices'  => array(
                      '1' => '1'
                    ),
                    'required' => false, 'label' => ' '
                ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'UJM\ExoBundle\Entity\Label',
            ));
    }

    public function getName()
    {
        return 'ujm_exobundle_labeltype';
    }
}