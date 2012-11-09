<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DirectoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');
        $builder->add('shareType', 'choice', array(
            'choices' => array(true => 'public', false => 'private'),
            'multiple' => false,
            'expanded' => false,
            'label' => 'sharable'
        ));
    }

    public function getName()
    {
        return 'directory_form';
    }

    public function getDefaultOptions(array $options)
    {
       return array(
           'translation_domain' => 'platform'
       );
    }

}
