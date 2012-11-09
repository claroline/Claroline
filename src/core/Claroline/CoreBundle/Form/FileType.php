<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'file');
        $builder->add(
            'license',
            'entity',
            array(
                'class' => 'Claroline\CoreBundle\Entity\License',
                'property' => 'name'
                )
        );
        $builder->add(
            'shareType',
            'choice',
            array(
                'choices' => array(true => 'public', false => 'private'),
                'multiple' => false,
                'expanded' => false,
                'label' => 'sharable'
            )
        );
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