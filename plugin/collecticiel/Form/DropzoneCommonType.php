<?php

namespace Innova\CollecticielBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DropzoneCommonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $defaultDateTimeOptions = [
            'required' => false,
            'read_only' => false,
            'component' => true,
            'autoclose' => true,
            'language' => $options['language'],
            'date_format' => $options['date_format'],
            'format' => $options['date_format'],
        ];

        $builder
            ->add(
                'stayHere',
                HiddenType::class,
                [
                    'mapped' => false,
                ]
            )
            ->add(
                'autoCloseForManualStates',
                HiddenType::class,
                [
                    'mapped' => false,
                ]
            )
            ->add(
                'instruction',
                'tinymce',
                [
                    'required' => false,
                ]
            )

            ->add('allowWorkspaceResource', CheckboxType::class, ['required' => false])
            ->add('allowUpload', CheckboxType::class, ['required' => false])
            ->add('allowUrl', CheckboxType::class, ['required' => false])
            ->add('allowRichText', CheckboxType::class, ['required' => false])

            ->add('successMessage', 'tinymce', ['required' => false])
            ->add('failMessage', 'tinymce', ['required' => false])

            ->add(
                'manualPlanning',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices' => [
                            true => 'manualPlanning',
                            false => 'sheduleByDate',
                        ],
                    'expanded' => true,
                    'multiple' => false,
                    ]
            )
            ->add('manualState', ChoiceType::class,
                [
                'choices' => [
                    'allowDrop' => 'allowDropManualState',
                    'finished' => 'finishedManualState',
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('startAllowDrop', 'datetimepicker', $defaultDateTimeOptions)
            ->add('endAllowDrop', 'datetimepicker', $defaultDateTimeOptions);

        $builder
            // Accusé de réception. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('published', CheckboxType::class,
                [
                    'attr' => [],
                    'mapped' => false,
                    'required' => false,
                ]
            );

        $builder
            // Accusé de réception. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('returnReceipt', CheckboxType::class,
                [
                    'required' => false,
                ]
            )
            // Picture. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('picture', CheckboxType::class,
                [
                    'required' => false,
                ]
            )
            // Username. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('username', CheckboxType::class,
                [
                    'required' => false,
                ]
            )

            // EvaluationType. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('evaluationType', ChoiceType::class,
                [
                    'choices' => [
                        'noEvaluation' => 'noEvaluation',
                        'notation' => 'notation',
                        'ratingScale' => 'ratingScale',
                    ],
                    'expanded' => false,
                    'multiple' => false,
                    ]
            )

            // Notation maxi. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('maximumNotation', IntegerType::class,
                [
                    'required' => true,
                    'attr' => [
                        'min' => 0,
                        'max' => 9999,
                        'class' => 'form-control-notation',
                    ],
                ]
            )
            ;
    }

    public function getName()
    {
        return 'innova_collecticiel_common_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'language' => 'fr',
                'translation_domain' => 'innova_collecticiel',
                'date_format' => DateType::HTML5_FORMAT,
            ]
        );
    }
}
