<?php

namespace Innova\PathBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array ('required' => true));
        $builder->add('description', 'tinymce', array ('required' => false));
        $builder->add('save','submit',array('label'=>'Save', 'attr'=>array('class'=>'btn btn-primary','data-first-button')));
    }

    public function getName()
    {
        return 'innova_step';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array (
            'data_class' => 'Innova\PathBundle\Entity\Step',
        ));
        
        return $this;
    }
} 