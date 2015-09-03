<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WordResponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('response', 'text')
            ->add('score', 'text', array('attr' => array('placeholder'=>'points')))
            ->add(
                'caseSensitive', 'checkbox', array(
                    'required' => false,
                    'attr' => array('title' => 'case_sensitive')
                )
            )
            ->add(
                   'feedback', 'textarea', array(
                   'required' => false, 'label' => ' ',
                   'attr' => array('class'=>'form-control',
                                   'data-new-tab' => 'yes',
                                   'placeholder' => 'feedback_answer_check',
                                   'style' => 'height:34px;',
                                   'translation_domain' => 'ujm_exo'
                       ),
                       
                  )
            )
            //->add('interactionopen')
            //->add('hole')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UJM\ExoBundle\Entity\WordResponse',
        ));
    }

    public function getName()
    {
        return 'ujm_exobundle_wordresponsetype';
    }
}
