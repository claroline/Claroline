<?php

namespace UJM\ExoBundle\Form\Player;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExercisePlayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name', 'text', array(
                    'data' => 'exercise'
                )
            )
            ->add('description', 'tinymce', array(
                    'attr' => array('data-new-tab' => 'yes'),
                    'label' => 'Description', 'required' => false
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'UJM\ExoBundle\Entity\Player\ExercisePlayer',
            )
        );
    }

    public function getName()
    {
        return 'exercise_player_type';
    }
}
