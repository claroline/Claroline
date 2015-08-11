<?php

namespace FormaLibre\PresenceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class ReleveType extends AbstractType

{
    private $stats;
    
    public function __construct($param) 
    {
        $this->stats=$param; 
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) 
                
           {$builder ->add('idPresence','hidden')
                     ->add('Pres', 'submit')
                     ->add('Abs', 'submit')
                     ->add('Ret', 'submit');
           foreach ($this->stats as $stat)
           {
            $builder ->add($stat->getStatusName(),'submit');
           }
    }       
    
    public function getName() {
        
        return 'Releve';
    }
}