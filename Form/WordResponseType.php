<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WordResponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('response', 'text')
            ->add('score', 'text', array('attr' => array('class'=>'col-md-1', 'placeholder'=>'point')))
            ->add(
                'caseSensitive', 'checkbox', array(
                    'required' => false,
                    'attr' => array('title' => 'WordResonse.caseSensitive')
                )
            )
            //->add('interactionopen')
            //->add('hole')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
