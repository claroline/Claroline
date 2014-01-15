<?php

namespace Innova\PathBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class NonDigitalResourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');
        $builder->add('nonDigitalResourceType', 'entity', array(
            'class' => 'InnovaPathBundle:NonDigitalResourceType',
            'property' => 'name',
        ));
        $builder->add('description', 'tinymce');
    }

    public function getName()
    {
        return 'innova_non_digital_resource';
    }
    
    public function getDefaultOptions()
    {
        return array (
            'data_class' => 'Innova\PathBundle\Entity\NonDigitalResource',
        );
    }
} 