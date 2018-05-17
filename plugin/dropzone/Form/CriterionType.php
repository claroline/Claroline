<?php

namespace Icap\DropzoneBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CriterionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('instruction', 'tinymce', array())
            ->add('totalCriteriaColumn', HiddenType::class, array())
            ->add('allowCommentInCorrection', HiddenType::class, array())
            ->add('correctionsGlobalInstructions', HiddenType::class, array('mapped' => false));
    }

    public function getName()
    {
        return 'icap_dropzone_criterion_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'icap_dropzone',
        ));
    }
}
