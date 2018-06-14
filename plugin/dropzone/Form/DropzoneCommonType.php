<?php

namespace Icap\DropzoneBundle\Form;

use Claroline\CoreBundle\Form\Field\DateTimePickerType;
use Claroline\CoreBundle\Form\Field\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DropzoneCommonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $defaultDateTimeOptions = [
            'required' => false,
            'component' => true,
            'autoclose' => true,
            'language' => $options['language'],
            'date_format' => $options['date_format'],
        ];

        $builder
            ->add('stayHere', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('autoCloseForManualStates', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('instruction', TinymceType::class, [
                'required' => false,
            ])

            ->add('allowWorkspaceResource', CheckboxType::class, ['required' => false])
            ->add('allowUpload', CheckboxType::class, ['required' => false])
            ->add('allowUrl', CheckboxType::class, ['required' => false])
            ->add('allowRichText', CheckboxType::class, ['required' => false])

            ->add('peerReview', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'Standard evaluation' => false,
                    'Peer review evaluation' => true,
                ],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('expectedTotalCorrection', IntegerType::class, ['required' => true])

            ->add('displayNotationToLearners', CheckboxType::class, ['required' => false])
            ->add('diplayCorrectionsToLearners', CheckboxType::class, ['required' => false])
            ->add('allowCorrectionDeny', CheckboxType::class, ['required' => false])
            ->add('displayNotationMessageToLearners', CheckboxType::class, ['required' => false])
            ->add('successMessage', TinymceType::class, ['required' => false])
            ->add('failMessage', TinymceType::class, ['required' => false])
            ->add('minimumScoreToPass', IntegerType::class, ['required' => true])

            ->add('manualPlanning', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'manualPlanning' => true,
                    'sheduleByDate' => false,
                ],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false,
            ])

            ->add('manualState', ChoiceType::class, [
                'choices' => [
                    'notStartedManualState' => 'notStarted',
                    'allowDropManualState' => 'allowDrop',
                    'peerReviewManualState' => 'peerReview',
                    'allowDropAndPeerReviewManualState' => 'allowDropAndPeerReview',
                    'finishedManualState' => 'finished',
                ],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('autoCloseOpenedDropsWhenTimeIsUp', CheckboxType::class, ['required' => false])
            ->add('notifyOnDrop', CheckboxType::class, ['required' => false])
            /*
             *
             ->add('startAllowDrop', 'datetime', array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => false, 'required' => false))
            ->add('endAllowDrop', 'datetime', array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => false, 'required' => false))
            ->add('startReview', 'datetime', array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => false, 'required' => false))
            ->add('endReview', 'datetime', array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => false, 'required' => false))
            */
            ->add('startAllowDrop', DateTimePickerType::class, $defaultDateTimeOptions)
            ->add('endAllowDrop', DateTimePickerType::class, $defaultDateTimeOptions)
            ->add('startReview', DateTimePickerType::class, $defaultDateTimeOptions)
            ->add('endReview', DateTimePickerType::class, $defaultDateTimeOptions);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Icap\DropzoneBundle\Entity\Dropzone',
                'language' => 'en',
                'translation_domain' => 'icap_dropzone',
                'date_format' => DateType::HTML5_FORMAT,
            ]
        );
    }
}
