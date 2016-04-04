<?php

namespace FormaLibre\PresenceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PresenceType extends AbstractType

{ 
    public function buildForm(FormBuilderInterface $builder, array $options) 
    { 
        $builder
             ->add(
                 'userStudent', 
                 'entity', 
                 array(
                     'required' => false, 
                     'disabled' => true, 
                     'read_only' => true, 
                     'class' => 'Claroline\CoreBundle\Entity\User', 
                     'property' => 'UserName'
                 )
             )
             ->add (
                 'Status',
                 'entity',
                 array (
                     'multiple'  => false,
                     'expanded'  => true, 
                     'label'=>'Status:',
                     'class' => 'FormaLibre\PresenceBundle\Entity\Status',
                     'data_class' => null,
                     'property' => 'statusName'

                 )
              
             );

    }   
    
    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'FormaLibre\PresenceBundle\Entity\Presence',
            ));
    }

    
    public function getName() {
        
        return 'Releve';
    }
}
