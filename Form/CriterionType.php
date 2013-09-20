<?php

namespace ICAP\DropZoneBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CriterionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('instruction', 'textarea', array(
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'advanced'
                )
            ))
            ->add('totalCriteriaColumn', 'hidden', array())
            ->add('allowCommentInCorrection', 'hidden', array());
    }

    public function getName()
    {
        return 'icap_dropzone_criterion_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array());
    }
}