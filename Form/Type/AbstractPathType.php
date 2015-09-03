<?php

namespace Innova\PathBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractPathType extends AbstractType
{
    abstract function getName();
    
    public function buildForm(FormBuilderInterface $builder, array $options = array ())
    {
        $builder->add('name',             'text',     array ('required' => true));
        $builder->add('description',      'text',     array ('required' => false));
        $builder->add('breadcrumbs',      'checkbox', array ('required' => false));
        $builder->add('summaryDisplayed', 'checkbox', array ('required' => false));
        $builder->add('structure',        'hidden',   array ('required' => true));
    }
    
    abstract function getDefaultOptions();
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults($this->getDefaultOptions());
    
        return $this;
    }
}
