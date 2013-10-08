<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class WidgetInstanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array('constraints' => new NotBlank()));
        $builder->add(
            'widget',
            'entity',
            array(
                'class' => 'Claroline\CoreBundle\Entity\Widget\Widget',
                'expanded' => false,
                'multiple' => false
            )
        );
    }

    public function getName()
    {
        return 'widget_instance_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'widget'
            )
        );
    }
}