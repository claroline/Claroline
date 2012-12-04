<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'hidden', array('data' => 'tmpname'));
        $builder->add('file', 'file', array('required' => false));
    }

    public function getName()
    {
        return 'file_form';
    }

    public function getDefaultOptions(array $options)
    {
       return array(
           'translation_domain' => 'platform'
       );
    }
}