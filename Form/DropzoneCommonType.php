<?php

namespace Innova\CollecticielBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

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
            'date_format'   => $options['date_format'],
            'format'        => $options['date_format'],
        );

        $builder
            ->add('stayHere', 'hidden', array(
                'mapped' => false,
            ))
            ->add('autoCloseForManualStates', 'hidden', array(
                "mapped" => false,
            ))
            ->add('instruction', 'tinymce', array(
                'required' => false,
            ))

            ->add('allowWorkspaceResource', 'checkbox', array('required' => false))
            ->add('allowUpload', 'checkbox', array('required' => false))
            ->add('allowUrl', 'checkbox', array('required' => false))
            ->add('allowRichText', 'checkbox', array('required' => false))

            ->add('successMessage', 'tinymce', array('required' => false))
            ->add('failMessage', 'tinymce', array('required' => false))

            ->add('manualPlanning', 'choice', array(
                'required' => true,
                'choices' => array(
                    true => 'manualPlanning',
                    false => 'sheduleByDate',
                ),
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('manualState', 'choice', array(
                'choices' => array(
                    'allowDrop' => 'allowDropManualState',
                    'finished' => 'finishedManualState',

                ),
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('startAllowDrop', 'datetimepicker', $defaultDateTimeOptions)
            ->add('endAllowDrop', 'datetimepicker', $defaultDateTimeOptions)

            // Publication. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('publication', 'checkbox', array('mapped' => false, 'required' => false))
            ;
    }

    public function getName()
    {
        return 'innova_collecticiel_common_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'language' => 'fr',
                'translation_domain' => 'innova_collecticiel',
                'date_format'     => DateType::HTML5_FORMAT,
            )
        );
    }
}
