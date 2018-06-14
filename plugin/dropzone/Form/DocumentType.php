<?php

namespace Icap\DropzoneBundle\Form;

use Claroline\CoreBundle\Form\Field\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
{
    private $name = 'icap_dropzone_document_file_form';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (TextType::class === $options['documentType']) {
            $this->setName('icap_dropzone_document_file_form_text');
            $builder->add('document', TinymceType::class, [
                'required' => true,
            ]);
        } elseif ('file' === $options['documentType']) {
            $this->setName('icap_dropzone_document_file_form_file');
            $builder->add('document', FileType::class, ['required' => true, 'label' => 'file document']);
        } elseif ('resource' === $options['documentType']) {
            $this->setName('icap_dropzone_document_file_form_resource');
            $builder->add(
                'document',
                HiddenType::class,
                [
                    'required' => true,
                    'label' => '',
                    'label_attr' => ['style' => 'display: none;'],
                ]
            );
        } else {
            $this->setName('icap_dropzone_document_file_form_url');
            $builder->add('document', UrlType::class, ['required' => true, 'label' => 'url document']);
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
        $resolver->setDefaults([
            'documentType' => UrlType::class,
            'translation_domain' => 'icap_dropzone',
        ]);
    }
}
