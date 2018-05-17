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
            ->add('goBack', HiddenType::class, array(
                'mapped' => false,
            ))
            ->add('correctionInstruction', 'tinymce', array('required' => false))
            ->add('totalCriteriaColumn', NumberType::class, array('required' => true))
            ->add('allowCommentInCorrection', CheckboxType::class, array('required' => false))
            ->add('forceCommentInCorrection', CheckboxType::class, array('required' => false))
            ->add('recalculateGrades', HiddenType::class, array('mapped' => false));
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
