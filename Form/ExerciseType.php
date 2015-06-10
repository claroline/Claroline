<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExerciseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name', 'hidden', array(
                    'data' => 'exercise'
                )
            )
            ->add(
                'title', 'text', array(
                    'label' => 'title'
                )
            )
            ->add('description', 'tinymce', array(
                    'attr' => array('data-new-tab' => 'yes'),
                    'label' => 'Description', 'required' => false
                )
            )
            ->add(
                'shuffle', 'checkbox', array(
                    'required' => false, 'label' => 'Exercise.shuffle'
                )
            )
            ->add(
                'nbQuestion', 'text', array(
                    'label' => 'number of questions to draw',
                    'required' => false
                )
            )
            ->add(
                'keepSameQuestion', 'checkbox', array(
                    'required' => false, 'label' => 'Exercise.keepSameQuestion'
                )
            )
            //->add('dateCreate')
            ->add(
                'duration', 'text', array(
                    'label' => 'Exercise.duration'
                )
            )
            //->add('nbQuestionPage')
            ->add(
                'doprint', 'checkbox', array(
                    'required' => false, 'label' => 'print paper'
                )
            )
            ->add(
                'maxAttempts', 'text', array(
                    'label' => 'maximum number of tries'
                )
            )
            //->add('correctionMode', 'text', array('label' => 'Availability of correction'))
            ->add(
                'correctionMode', 'choice', array(
                    'label' => 'availability_of_correction',
                    'choices' => array(
                        '1' => 'at_the_end_of_assessment',
                        '2' => 'after_the_last_attempt',
                        '3' => 'From',
                        '4' => 'Never'
                    )
                )
            )
            ->add(
                'dateCorrection', 'datetime', array(
                    'widget' => 'single_text',
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy H:mm:ss',
                    'attr' => array('data-format' => 'dd/MM/yyyy H:mm:ss'),
                    'label' => 'correction_date',
                )
            )
            ->add(
                'markMode', 'choice', array(
                    'label' => 'availability_of_score',
                    'choices' => array(
                        '1' => 'at_the_same_time_that_the_correction',
                        '2' => 'at_the_end_of_assessment'
                    )
                )
            )
            ->add(
                'start_date', 'datetime', array(
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy H:mm:ss',
                'attr' => array('data-format' => 'dd/MM/yyyy H:mm:ss'),
                'label' => 'start date',
                )
            )
            ->add(
                'useDateEnd', 'checkbox', array(
                    'required' => false, 'label' => 'use date of end'
                )
            )
            ->add(
                'end_date', 'datetime', array(
                    'widget' => 'single_text',
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy H:mm:ss',
                    'attr' => array('data-format' => 'dd/MM/yyyy H:mm:ss'),
                    'label' => 'Exercise.end_date',
                )
            )
            ->add(
                'dispButtonInterrupt', 'checkbox', array(
                    'required' => false, 'label' => 'test exit'
                )
            )
            ->add(
                'lockAttempt', 'checkbox', array(
                    'required' => false, 'label' => 'lock attempt'
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'UJM\ExoBundle\Entity\Exercise',
            )
        );
    }

    public function getName()
    {
        return 'ujm_exobundle_exercisetype';
    }
}
