<?php

namespace Claroline\DocumentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('file', 'file');
    }

    public function getName()
    {
        return 'Document_Form';
        //return 'Claroline_DocumentBundle_DocumentType';
    }
}