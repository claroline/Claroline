<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Organization;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OrganizationType extends AbstractType
{
    public function __construct()
    {
        $this->forApi = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                    'required' => true,
                )
            );

        $builder
            ->add('parent', 'entity', array(
                    'class' => 'ClarolineCoreBundle:Organization\Organization',
                    'property' => 'name',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                )
            );
    }

    public function getName()
    {
        return 'organization_form';
    }

    public function enableApi()
    {
        $this->forApi = true;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $default = array('translation_domain' => 'platform');
        if ($this->forApi) {
            $default['csrf_protection'] = false;
        }

        $resolver->setDefaults($default);
    }
}
