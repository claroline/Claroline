<?php

namespace Innova\CollecticielBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CriterionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('instruction', 'tinymce', array())
            ->add('totalCriteriaColumn', 'hidden', array())
            ->add('allowCommentInCorrection', 'hidden', array())
            ->add('correctionsGlobalInstructions', 'hidden', array('mapped' => false));
    }

    public function getName()
    {
        return 'innova_collecticiel_criterion_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'innova_collecticiel',
        ));
    }
}
