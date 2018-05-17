<?php

namespace Innova\CollecticielBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
{
    private $name = 'innova_collecticiel_document_file_form';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['documentType'] == TextType::class) {
            $this->setName('innova_collecticiel_document_file_form_text');

            $builder->add('title', TextType::class, array(
                          'required' => true, )
                        );
            $builder->add('document', 'tinymce', array(
                          'required' => true,
                        ));
        } elseif ($options['documentType'] == 'file') {
            $this->setName('innova_collecticiel_document_file_form_file');
            $builder->add('document', FileType::class, array('required' => true, 'label' => 'file document'));
        } elseif ($options['documentType'] == 'resource') {
            $this->setName('innova_collecticiel_document_file_form_resource');
            $builder->add(
                'document',
                HiddenType::class,
                array(
                    'required' => true,
                    'label' => '',
                    'label_attr' => array('style' => 'display: none;'),
                )
            );
        } else {
            $this->setName('innova_collecticiel_document_file_form_url');
            $builder->add('document', UrlType::class, array(
                          'required' => true,
                          'label' => 'url document', )
                         );
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'documentType' => UrlType::class,
            'translation_domain' => 'innova_collecticiel',
        ));
    }
}
