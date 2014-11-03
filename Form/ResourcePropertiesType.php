<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResourcePropertiesType extends AbstractType
{
    private $creator;

    public function __construct($creator)
    {
        $this->creator = $creator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');
        $builder->add(
            'newIcon',
            'file',
            array(
                'required' => false,
                'mapped' => false
            )
        );
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
            'published',
            'checkbox',
            array( 'required' => true)
        );
        $builder->add(
            'accessibleFrom',
            'date',
            array(
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd'
            )
        );
        $builder->add(
            'accessibleUntil',
            'date',
            array(
                'required' => false,
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
            'text',
            array(
                'data' => $this->creator,
                'mapped' => false,
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
        $resolver->setDefaults(array('translation_domain' => 'resource'));
    }
}
