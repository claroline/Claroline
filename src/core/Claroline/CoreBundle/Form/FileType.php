<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FileType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', 'file');
        $builder->add('license', 'entity', array('class' => 'ClarolineCoreBundle:License', 'property' => 'name'));
        $builder->add('shareType', 'choice', array(
            'choices' => array(true => 'public', false => 'private'),
            'multiple' => false,
            'expanded' => true,
            'label' => 'sharable'
        ));
    }

    public function getName()
    {
        return 'file_form';
    }
}