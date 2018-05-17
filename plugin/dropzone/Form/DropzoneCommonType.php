<?php

namespace Icap\DropzoneBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DropzoneCommonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $defaultDateTimeOptions = array(
            'required' => false,
            'read_only' => false,
            'component' => true,
            'autoclose' => true,
            'language' => $options['language'],
            'date_format' => $options['date_format'],
        );

        $builder
            ->add('stayHere', HiddenType::class, array(
                'mapped' => false,
            ))
            ->add('autoCloseForManualStates', HiddenType::class, array(
                'mapped' => false,
            ))
            ->add('instruction', 'tinymce', array(
                'required' => false,
            ))

            ->add('allowWorkspaceResource', CheckboxType::class, array('required' => false))
            ->add('allowUpload', CheckboxType::class, array('required' => false))
            ->add('allowUrl', CheckboxType::class, array('required' => false))
            ->add('allowRichText', CheckboxType::class, array('required' => false))

            ->add('peerReview', ChoiceType::class, array(
                'required' => true,
                'choices' => array(
                    'Standard evaluation' => false,
                    'Peer review evaluation' => true,
                ),
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('expectedTotalCorrection', IntegerType::class, array('required' => true))

            ->add('displayNotationToLearners', CheckboxType::class, array('required' => false))
            ->add('diplayCorrectionsToLearners', CheckboxType::class, array('required' => false))
            ->add('allowCorrectionDeny', CheckboxType::class, array('required' => false))
            ->add('displayNotationMessageToLearners', CheckboxType::class, array('required' => false))
            ->add('successMessage', 'tinymce', array('required' => false))
            ->add('failMessage', 'tinymce', array('required' => false))
            ->add('minimumScoreToPass', IntegerType::class, array('required' => true))

            ->add('manualPlanning', ChoiceType::class, array(
                'required' => true,
                'choices' => array(
                    'manualPlanning' => true,
                    'sheduleByDate' => false,
                ),
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false,
            ))

            ->add('manualState', ChoiceType::class, array(
                'choices' => array(
                    'notStartedManualState' => 'notStarted',
                    'allowDropManualState' => 'allowDrop',
                    'peerReviewManualState' => 'peerReview',
                    'allowDropAndPeerReviewManualState' => 'allowDropAndPeerReview',
                    'finishedManualState' => 'finished',
                ),
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('autoCloseOpenedDropsWhenTimeIsUp', CheckboxType::class, array('required' => false))
            ->add('notifyOnDrop', CheckboxType::class, array('required' => false))
            /*
             *
             ->add('startAllowDrop', 'datetime', array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => false, 'required' => false))
            ->add('endAllowDrop', 'datetime', array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => false, 'required' => false))
            ->add('startReview', 'datetime', array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => false, 'required' => false))
            ->add('endReview', 'datetime', array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => false, 'required' => false))
            */
            ->add('startAllowDrop', 'datetimepicker', $defaultDateTimeOptions)
            ->add('endAllowDrop', 'datetimepicker', $defaultDateTimeOptions)
            ->add('startReview', 'datetimepicker', $defaultDateTimeOptions)
            ->add('endReview', 'datetimepicker', $defaultDateTimeOptions);
    }

    public function getName()
    {
        return 'icap_dropzone_common_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Icap\DropzoneBundle\Entity\Dropzone',
                'language' => 'en',
                'translation_domain' => 'icap_dropzone',
                'date_format' => DateType::HTML5_FORMAT,
            )
        );
    }
}
