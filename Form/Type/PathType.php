<?php

namespace Innova\PathBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PathType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');
        $builder->add('description', 'text');
    }

    public function getName()
    {
        return 'path';
    }
    
    public function getDefaultOptions()
    {
        return array (
            'data_class' => 'Innova\PathBundle\Entity\Path',
        );
    }
} 