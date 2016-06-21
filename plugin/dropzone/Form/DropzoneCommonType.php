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
            ->add('stayHere', 'hidden', array(
                'mapped' => false,
            ))
            ->add('autoCloseForManualStates', 'hidden', array(
                'mapped' => false,
            ))
            ->add('instruction', 'tinymce', array(
                'required' => false,
            ))

            ->add('allowWorkspaceResource', 'checkbox', array('required' => false))
            ->add('allowUpload', 'checkbox', array('required' => false))
            ->add('allowUrl', 'checkbox', array('required' => false))
            ->add('allowRichText', 'checkbox', array('required' => false))

            ->add('peerReview', 'choice', array(
                'required' => true,
                'choices' => array(
                    'Standard evaluation' => false,
                    'Peer review evaluation' => true,
                ),
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('expectedTotalCorrection', 'integer', array('required' => true))

            ->add('displayNotationToLearners', 'checkbox', array('required' => false))
            ->add('diplayCorrectionsToLearners', 'checkbox', array('required' => false))
            ->add('allowCorrectionDeny', 'checkbox', array('required' => false))
            ->add('displayNotationMessageToLearners', 'checkbox', array('required' => false))
            ->add('successMessage', 'tinymce', array('required' => false))
            ->add('failMessage', 'tinymce', array('required' => false))
            ->add('minimumScoreToPass', 'integer', array('required' => true))

            ->add('manualPlanning', 'choice', array(
                'required' => true,
                'choices' => array(
                    'manualPlanning' => true,
                    'sheduleByDate' => false,
                ),
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false,
            ))

            ->add('manualState', 'choice', array(
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
            ->add('autoCloseOpenedDropsWhenTimeIsUp', 'checkbox', array('required' => false))
            ->add('notifyOnDrop', 'checkbox', array('required' => false))
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
