<?php

namespace Innova\PathBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PathWidgetConfigType extends AbstractType
{
    public function getName()
    {
        return 'innova_path_widget_config';
    }

    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $builder->add('status', 'choice', [
            'choices' => [
                'draft' => 'draft',
                'published' => 'published',
                'modified' => 'modified',
            ],
            'multiple' => true,
            'required' => false,
        ]);

        $builder->add('removeTags', 'hidden', [
            'mapped' => false,
            'required' => false,
        ]);

        $builder->add('tags', 'text', [
            'mapped' => false,
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Innova\PathBundle\Entity\PathWidgetConfig',
            'translation_domain' => 'widget',
        ]);

        return $this;
    }
}
