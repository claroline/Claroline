<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class LinkType extends AbstractType
{

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('url', 'text');
        $builder->add('name', 'text');
        $builder->add('resourceType', 'entity', array('class' => 'ClarolineCoreBundle:Resource\ResourceType', 'property' => 'type'));
        $builder->add('isSharable', 'choice', array(
            'choices' => array(true => ' '),
            'multiple' => true,
            'expanded' => true,
            'label' => 'sharable'
        ));
    }

    public function getName()
    {
        return 'link_form';
    }

}