<?php

namespace Icap\PortfolioBundle\Form\Type\Widgets;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AbstractWidgetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label',  'text')
            ->add('column', 'integer')
            ->add('row',    'integer')
            ->add('col',    'integer', ['property_path' => 'column'])
            ->add('sizeX',  'integer')
            ->add('sizeY',  'integer');
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return '';
    }
}
