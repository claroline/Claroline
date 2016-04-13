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

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Claroline\CoreBundle\Form\Angular\AngularType;

class OrganizationParametersType extends AngularType
{
    public function __construct($ngAlias = 'eofm')
    {
        $this->forApi = false;
        $this->ngAlias = $ngAlias;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required' => true, 'label' => 'name'))
            ->add('email', 'email', array('required' => false, 'label' => 'email'))
            ->add(
                'locations',
                'entity',
                array(
                    'label' => 'locations',
                    'class' => 'Claroline\CoreBundle\Entity\Organization\Location',
                    'expanded' => true,
                    'multiple' => true,
                    'property' => 'name',
                )
            )
            ->add(
                'administrators',
                'userpicker',
                array(
                    'multiple' => true,
                    'label' => 'administrators',
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
        $default['ng-model'] = 'organization';
        $default['ng-controllerAs'] = $this->ngAlias;

        $resolver->setDefaults($default);
    }
}
