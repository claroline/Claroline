<?php

namespace Icap\DropzoneBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['documentType'] == 'text') {
            $builder->add('document', 'textarea',  array(
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'advanced'
                ),
                'required' => true,
                'label' => 'text document'
            ));
        } else if ($options['documentType'] == 'file') {
            $builder->add('document', 'file',  array('required' => true, 'label' => 'file document'));
        } else if ($options['documentType'] == 'resource') {
           $builder->add(
               'document',
               'hidden',
               array(
                   'required' => true,
                   'label' => '',
                   'label_attr' => array('style' => 'display: none;')
               )
           );
        } else {
            $builder->add('document', 'url',  array('required' => true, 'label' => 'url document'));
        }
    }

    public function getName()
    {
        return 'icap_dropzone_document_file_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'documentType' => 'url',
        ));
    }
}