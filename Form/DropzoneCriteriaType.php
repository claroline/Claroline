<?php

namespace Innova\CollecticielBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DropzoneCriteriaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('goBack', 'hidden', array(
                'mapped' => false
            ))
            ->add('correctionInstruction', 'tinymce', array('required' => false))
            ->add('totalCriteriaColumn', 'number', array('required' => true))
// Voir issue 252 InnovaERV
//            ->add('allowCommentInCorrection', 'checkbox', array('required' => false))
//            ->add('forceCommentInCorrection', 'checkbox', array('required' => false))
            ->add('recalculateGrades', 'hidden', array('mapped' => false));
    }

    public function getName()
    {
        return 'innova_collecticiel_criteria_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'innova_collecticiel',
        ));
    }
}