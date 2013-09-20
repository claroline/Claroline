<?php

namespace ICAP\DropZoneBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CorrectionReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('reportComment', 'textarea', array(
            'attr' => array(
                'class' => 'tinymce',
                'data-theme' => 'advanced'
            ),
            'label_attr' => array(
                'style' => 'display: none;'
            ),
            'required' => true
        ));
    }

    public function getName()
    {
        return 'icap_dropzone_correction_report_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }
}