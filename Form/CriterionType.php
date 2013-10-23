<?php

namespace Icap\DropzoneBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CriterionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('instruction', 'tinymce', array())
            ->add('totalCriteriaColumn', 'hidden', array())
            ->add('allowCommentInCorrection', 'hidden', array());
    }

    public function getName()
    {
        return 'icap_dropzone_criterion_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'icap_dropzone'
        ));
    }
}