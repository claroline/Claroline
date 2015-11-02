<?php

namespace Innova\PathBundle\Form\Type;

class PathTemplateType extends AbstractPathType
{
    public function getName()
    {
        return 'innova_path_template';
    }
    
    public function getDefaultOptions()
    {
        return array (
            'data_class' => 'Innova\PathBundle\Entity\Path\PathTemplate',
        );
    }
}
