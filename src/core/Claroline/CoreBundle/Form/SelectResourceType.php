<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\AbstractType;
use Claroline\CoreBundle\Entity\Resource\ResourceType;

class SelectResourceType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
         $builder->add('type', 
             'entity', array(
             'class' => 'Claroline\CoreBundle\Entity\Resource\ResourceType', 
             'expanded' => false, 
             'multiple' => false, 
             'property' => 'type', 
             'read_only' => false
                     )
         );
    }
    
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Claroline\CoreBundle\Entity\Resource\ResourceType',
        );
    }
    
    public function getName()
    {
        return 'select_resource_form';
    }
}