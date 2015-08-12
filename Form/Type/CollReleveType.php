<?php

namespace FormaLibre\PresenceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CollReleveType extends AbstractType

{
//    private $stats;
//    
//    public function __construct($param) 
//    {
//        $this->stats=$param; 
//    }

    public function buildForm(FormBuilderInterface $builder, array $options) 
                
           {$builder->add('releves', 'collection', array('type' => new ReleveType(),
                                                         'allow_add'    => true,
                                                         'allow_delete' => true));     
           }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'FormaLibre\PresenceBundle\Entity\FormColl',
            ));
    }

    public function getName() {
        
        return 'CollReleve';
    }
}

