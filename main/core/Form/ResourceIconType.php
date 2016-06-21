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

class ResourceIconType extends AbstractType
{
    private $creator;

    public function __construct($creator)
    {
        $this->creator = $creator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            array('label' => 'name', 'disabled' => true)
        );
        $builder->add(
            'newIcon',
            'file',
            array(
                'required' => false,
                'mapped' => false,
                'label' => 'icon',
            )
        );
        $builder->add(
            'creationDate',
            'date',
            array(
                'disabled' => true,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'label' => 'creation_date',
            )
        );
        $builder->add(
            'creator',
            'text',
            array(
                'data' => $this->creator,
                'mapped' => false,
                'disabled' => true,
                'label' => 'creator',
            )
        );
    }

    public function getName()
    {
        return 'resource_icon_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
