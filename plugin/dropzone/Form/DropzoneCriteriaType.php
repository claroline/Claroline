<?php

namespace Icap\DropzoneBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DropzoneCriteriaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('goBack', 'hidden', array(
                'mapped' => false,
            ))
            ->add('correctionInstruction', 'tinymce', array('required' => false))
            ->add('totalCriteriaColumn', 'number', array('required' => true))
            ->add('allowCommentInCorrection', 'checkbox', array('required' => false))
            ->add('forceCommentInCorrection', 'checkbox', array('required' => false))
            ->add('recalculateGrades', 'hidden', array('mapped' => false));
    }

    public function getName()
    {
        return 'icap_dropzone_criteria_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'icap_dropzone',
        ));
    }
}
