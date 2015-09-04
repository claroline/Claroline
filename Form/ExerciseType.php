<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ExerciseType extends AbstractType
{
    private $isCreationForm;

    /**
     * @param bool $isCreationForm Whether the form is used in a resource creation context
     */
    public function __construct($isCreationForm = false)
    {
        $this->isCreationForm = $isCreationForm;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text', [
            'label' => 'title',
            'constraints' => [new NotBlank()]
        ]);

        if ($this->isCreationForm) {
            return $builder->add('publish', 'checkbox', [
                'required' => true,
                'mapped' => false,
                'label' => 'publish_resource',
                'translation_domain' => 'platform'
           ]);
        }

        $builder
            ->add('name', 'hidden', [
                'data' => 'exercise'
            ])
            ->add('description', 'tinymce', [
                'attr' => ['data-new-tab' => 'yes'],
                'label' => 'Description',
                'required' => false
            ])
            ->add('shuffle', 'checkbox', [
                'required' => false,
                'label' => 'random_questions'
            ])
            ->add('nbQuestion', 'text', [
                'label' => 'number_questions_draw',
                'required' => false
            ])
            ->add('keepSameQuestion', 'checkbox', [
                'required' => false,
                'label' => 'keep_same_question'
            ])
            ->add('duration', 'text', [
                'label' => 'duration'
            ])
            ->add('doprint', 'checkbox', [
                'required' => false,
                'label' => 'print_paper'
            ])
            ->add('maxAttempts', 'text', [
                'label' => 'maximum_tries'
            ])
            ->add('correctionMode', 'choice', [
                'label' => 'availability_of_correction',
                'choices' => [
                    '1' => 'at_the_end_of_assessment',
                    '2' => 'after_the_last_attempt',
                    '3' => 'from',
                    '4' => 'never'
                ]
            ])
            ->add('dateCorrection', 'datetime', [
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy H:mm:ss',
                'attr' => ['data-format' => 'dd/MM/yyyy H:mm:ss'],
                'label' => 'correction_date',
            ])
            ->add('markMode', 'choice', [
                'label' => 'availability_of_score',
                'choices' => [
                    '1' => 'at_the_same_time_that_the_correction',
                    '2' => 'at_the_end_of_assessment'
                ]
            ])
            ->add('start_date', 'datetime', [
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy H:mm:ss',
                'attr' => ['data-format' => 'dd/MM/yyyy H:mm:ss'],
                'label' => 'start_date',
            ])
            ->add('useDateEnd', 'checkbox', [
                'required' => false,
                'label' => 'use_date_end'
            ])
            ->add('end_date', 'datetime', [
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy H:mm:ss',
                'attr' => ['data-format' => 'dd/MM/yyyy H:mm:ss'],
                'label' => 'end_date',
            ])
            ->add('dispButtonInterrupt', 'checkbox', [
                'required' => false,
                'label' => 'test_exit'
            ])
            ->add('lockAttempt', 'checkbox', [
                 'required' => false,
                 'label' => 'lock_attempt'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UJM\ExoBundle\Entity\Exercise',
            'translation_domain' => 'ujm_exo'
        ]);
    }

    public function getName()
    {
        return 'ujm_exo_exercise';
    }
}
