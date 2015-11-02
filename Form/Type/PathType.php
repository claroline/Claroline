<?php

namespace Innova\PathBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class PathType extends AbstractPathType
{
    public function buildForm(FormBuilderInterface $builder, array $options = array ())
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'published',
            'checkbox',
            array(
                'required' => true,
                'mapped' => false,
                'attr' => array ('checked' => 'checked')
            )
        );
    }
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
