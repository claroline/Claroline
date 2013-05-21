<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResourcePropertiesType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');
        $builder->add('userIcon', 'file', array('required' => false));
        $builder->add(
            'creationDate',
            'date',
            array(
                'disabled' => true,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd'
            )
        );
        $builder->add(
            'resourceType',
            'entity',
            array(
                'class' => 'Claroline\CoreBundle\Entity\Resource\ResourceType',
                'expanded' => false,
                'multiple' => false,
                'property' => 'name',
                'disabled' => true
            )
        );
        $builder->add(
            'creator',
            'entity',
            array(
                'class' => 'Claroline\CoreBundle\Entity\User',
                'expanded' => false,
                'multiple' => false,
                'property' => 'username',
                'disabled' => true
            )
        );
    }

    public function getName()
    {
        return 'resource_properties_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform'
                )
        );
    }
}