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
                'hidden',
                [
                    'mapped' => false,
                ]
            )
            ->add(
                'autoCloseForManualStates',
                'hidden',
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

            ->add('allowWorkspaceResource', 'checkbox', ['required' => false])
            ->add('allowUpload', 'checkbox', ['required' => false])
            ->add('allowUrl', 'checkbox', ['required' => false])
            ->add('allowRichText', 'checkbox', ['required' => false])

            ->add('successMessage', 'tinymce', ['required' => false])
            ->add('failMessage', 'tinymce', ['required' => false])

            ->add(
                'manualPlanning',
                'choice',
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
            ->add('manualState', 'choice',
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
            ->add('published', 'checkbox',
                [
                    'attr' => [],
                    'mapped' => false,
                    'required' => false,
                ]
            );

        $builder
            // Accusé de réception. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('returnReceipt', 'checkbox',
                [
                    'required' => false,
                ]
            )
            // Picture. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('picture', 'checkbox',
                [
                    'required' => false,
                ]
            )
            // Username. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('username', 'checkbox',
                [
                    'required' => false,
                ]
            )

            // EvaluationType. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('evaluationType', 'choice',
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
            ->add('maximumNotation', 'integer',
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
