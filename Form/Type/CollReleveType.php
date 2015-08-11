<?php

namespace FormaLibre\PresenceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class CollReleveType extends AbstractType

{
    public function buildForm(FormBuilderInterface $builder, array $options) 
                
           {$builder->add('releves', 'collection', array('type' => new ReleveType(),
                                                         'allow_add'    => true,
                                                         'allow_delete' => true))
                    ->add('test', 'text');
  
    }
    
    public function getName() {
        
        return 'CollReleve';
    }
}