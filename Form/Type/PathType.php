<?php

namespace Innova\PathBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class PathType extends AbstractPathType
{
    public function getName()
    {
        return 'innova_path';
    }
    
    public function getDefaultOptions()
    {
        return array (
            'data_class' => 'Innova\PathBundle\Entity\Path\Path',
        );
    }
} 