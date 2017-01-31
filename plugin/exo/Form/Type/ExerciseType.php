<?php

namespace UJM\ExoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExerciseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', ['required' => true]);
        $builder->add('published', 'checkbox', ['mapped' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UJM\ExoBundle\Entity\Exercise',
            'translation_domain' => 'resource',
        ]);
    }

    public function getName()
    {
        return 'exercise_form';
    }
}
