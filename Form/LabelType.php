<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LabelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'ordre', 'text'
            )
            ->add(
                'value', 'textarea', array(
                    'required' => true,
                    'label' => ' ', 'attr' => array(
                        'class' => 'labelVal form-control',
                        'style' => 'height:34px;',
                        'placeholder' => 'choice'
                    ),
                    'translation_domain' => 'ujm_exo'
                )
            )
            ->add(
                'scoreRightResponse', 'text', array(
                    'required' => true,
                    'label' => ' ', 'attr' => array('class' => 'labelScore', 'placeholder' => 'points'
                  ),
                    'translation_domain' => 'ujm_exo'))
                //add a field for correspondance, and will be replace by the our field
            ->add( "correspondance", "choice", array("mapped"=>false)
                  )
            
            ->add(
                'positionForce', 'checkbox', array(
                    'required' => false, 'label' => ' '
                ))
            ->add(
                   'feedback', 'textarea', array(
                   'required' => false, 'label' => ' ',
                   'attr' => array('class'=>'form-control',
                                   'data-new-tab' => 'yes',
                                   'placeholder' => 'feedback_answer_check',
                                   'style' => 'height:34px;'
                       ),
                       'translation_domain' => 'ujm_exo'
                  )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
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
