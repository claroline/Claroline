<?php

namespace Innova\PathBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

class PathType extends AbstractPathType
{
    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'published',
            'checkbox',
            [
                'required' => true,
                'mapped' => false,
                'attr' => ['checked' => 'checked'],
            ]
        );
    }

    public function getName()
    {
        return 'innova_path';
    }

    public function getDefaultOptions()
    {
        return [
            'data_class' => 'Innova\PathBundle\Entity\Path\Path',
        ];
    }
}
