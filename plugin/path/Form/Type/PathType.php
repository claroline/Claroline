<?php

namespace Innova\PathBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PathType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $builder->add(
            'name',
            'text', [
                'required' => true,
                'attr' => ['autofocus' => true],
            ]
        );
        $builder->add('description',                'tinymce',  ['required' => false]);
        $builder->add('breadcrumbs',                'checkbox', ['required' => false]);
        $builder->add('summaryDisplayed',           'checkbox', ['required' => false]);
        $builder->add('completeBlockingCondition',  'checkbox', ['required' => false]);
        $builder->add('manualProgressionAllowed',   'checkbox', ['required' => false]);
        $builder->add('structure',                  'hidden',   ['required' => true]);

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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Innova\PathBundle\Entity\Path\Path',
        ]);
    }
}
