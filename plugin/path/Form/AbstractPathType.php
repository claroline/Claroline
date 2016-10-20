<?php

namespace Innova\PathBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractPathType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $builder->add('name',                       'text',     [
            'required' => true,
            'attr' => ['autofocus' => true],
        ]);
        $builder->add('description',                'tinymce',  ['required' => false]);
        $builder->add('breadcrumbs',                'checkbox', ['required' => false]);
        $builder->add('summaryDisplayed',           'checkbox', ['required' => false]);
        $builder->add('completeBlockingCondition',  'checkbox', ['required' => false]);
        $builder->add('manualProgressionAllowed',   'checkbox', ['required' => false]);
        $builder->add('structure',                  'hidden',   ['required' => true]);
    }

    abstract public function getDefaultOptions();

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults($this->getDefaultOptions());

        return $this;
    }
}
