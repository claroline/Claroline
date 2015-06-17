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
            ->add('score', 'text', array('attr' => array('placeholder'=>'score_answer')))
            ->add(
                'caseSensitive', 'checkbox', array(
                    'required' => false,
                    'attr' => array('title' => 'case_sensitive')
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
