<?php

namespace Innova\PathBundle\Form\Type;

use Claroline\TagBundle\Form\TagType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PathWidgetConfigType extends AbstractType
{
    public function getName()
    {
        return 'innova_path_widget_config';
    }

    public function buildForm(FormBuilderInterface $builder, array $options = array ())
    {
        $builder->add('status', 'choice', array(
            'choices'   => array(
                'draft'     => 'draft',
                'published' => 'published',
                'modified'  => 'modified',
            ),
            'multiple'  => true,
        ));

        $builder->add(
            'tags',
            'text',
            array('mapped' => false)
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array (
            'data_class' => 'Innova\PathBundle\Entity\PathWidgetConfig',
            'translation_domain' => 'widget'
        ));

        return $this;
    }
}
