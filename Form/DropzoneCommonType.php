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
        $defaultDateTimeOptions = array(
            'required'      => false,
            'read_only'     => false,
            'component'     => true,
            'autoclose'     => true,
            'language'      => $options['language'],
            'format'        => $options['date_format']
        );

        $builder
            ->add('stayHere', 'hidden', array(
                'mapped' => false
            ))
            ->add('autoCloseForManualStates', 'hidden', array(
                "mapped" => false
            ))
            ->add('instruction', 'tinymce', array(
                'required' => false
            ))


            ->add('allowWorkspaceResource', 'checkbox', array('required' => false))
            ->add('allowUpload', 'checkbox', array('required' => false))
            ->add('allowUrl', 'checkbox', array('required' => false))
            ->add('allowRichText', 'checkbox', array('required' => false))

/*            ->add('peerReview', 'choice', array(
                'required' => true,
                'choices' => array(
                    true => 'Standard evaluation'
                ),
                'data' => true,
                'expanded' => true,
                'multiple' => false
            ))


            ->add('peerReview', 'choice',
                array(
                'required' => true,
                'choices' => array(
                    false => 'Standard evaluation',
                                    ),
                'expanded' => true
                     )
                )
*/
/*
Suppression suite demande #251
            ->add('expectedTotalCorrection', 'integer', array('required' => true))
            ->add('displayNotationToLearners', 'checkbox', array('required' => false))
*/
/* Suppression suite demande #45
           ->add('diplayCorrectionsToLearners','checkbox', array('required' => false)) */

/* Suppression suite demande #45
           ->add('allowCorrectionDeny','checkbox',array('required'=>false))
           ->add('displayNotationMessageToLearners', 'checkbox', array('required' => false))
*/

            ->add('successMessage','tinymce',array('required' => false))
            ->add('failMessage','tinymce',array('required' => false))
            ->add('minimumScoreToPass', 'integer', array('required' => true))

/*

            ->add('manualPlanning', 'choice', array(
                'required' => true,
                'choices' => array(
                    true => 'manualPlanning',
                    false => 'sheduleByDate'
                ),
                'expanded' => true,
                'multiple' => false
            ))
*/
            ->add('manualState', 'choice', array(
                'choices' => array(
                    'notStarted' => 'notStartedManualState',
                    'allowDrop' => 'allowDropManualState',
/* issue #251 InnovaERV
                    'peerReview' => 'peerReviewManualState',
                    'allowDropAndPeerReview' => 'allowDropAndPeerReviewManualState',
*/
                    'finished' => 'finishedManualState',
                ),
                'expanded' => true,
                'multiple' => false
            ))
            ->add('autoCloseOpenedDropsWhenTimeIsUp','checkbox', array('required' => false))
            ->add('notifyOnDrop', 'checkbox', array('required' => false))
            /*
             *
             ->add('startAllowDrop', 'datetime', array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => false, 'required' => false))
            ->add('endAllowDrop', 'datetime', array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => false, 'required' => false))
            ->add('startReview', 'datetime', array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => false, 'required' => false))
            ->add('endReview', 'datetime', array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => false, 'required' => false))
            ->add('startAllowDrop', 'datetimepicker', $defaultDateTimeOptions)
            ->add('endAllowDrop', 'datetimepicker', $defaultDateTimeOptions)
            */
            ->add('startReview', 'datetimepicker', $defaultDateTimeOptions)
            ->add('endReview', 'datetimepicker', $defaultDateTimeOptions);

    }

    public function getName()
    {
        return 'innova_collecticiel_common_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'language' => 'en',
                'translation_domain' => 'innova_collecticiel',
                'date_format'     => DateType::HTML5_FORMAT,
            )
        );
    }
}