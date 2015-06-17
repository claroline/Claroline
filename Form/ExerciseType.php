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
                    'required' => false, 'label' => 'random_questions'
                )
            )
            ->add(
                'nbQuestion', 'text', array(
                    'label' => 'number_questions_draw',
                    'required' => false
                )
            )
            ->add(
                'keepSameQuestion', 'checkbox', array(
                    'required' => false, 'label' => 'keep_same_question'
                )
            )
            //->add('dateCreate')
            ->add(
                'duration', 'text', array(
                    'label' => 'duration'
                )
            )
            //->add('nbQuestionPage')
            ->add(
                'doprint', 'checkbox', array(
                    'required' => false, 'label' => 'print_paper'
                )
            )
            ->add(
                'maxAttempts', 'text', array(
                    'label' => 'maximum_tries'
                )
            )
            //->add('correctionMode', 'text', array('label' => 'Availability of correction'))
            ->add(
                'correctionMode', 'choice', array(
                    'label' => 'availability_of_correction',
                    'choices' => array(
                        '1' => 'at_the_end_of_assessment',
                        '2' => 'after_the_last_attempt',
                        '3' => 'from',
                        '4' => 'never'
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
                'label' => 'start_date',
                )
            )
            ->add(
                'useDateEnd', 'checkbox', array(
                    'required' => false, 'label' => 'use_date_end'
                )
            )
            ->add(
                'end_date', 'datetime', array(
                    'widget' => 'single_text',
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy H:mm:ss',
                    'attr' => array('data-format' => 'dd/MM/yyyy H:mm:ss'),
                    'label' => 'end_date',
                )
            )
            ->add(
                'dispButtonInterrupt', 'checkbox', array(
                    'required' => false, 'label' => 'test_exit'
                )
            )
            ->add(
                'lockAttempt', 'checkbox', array(
                    'required' => false, 'label' => 'lock_attempt'
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
