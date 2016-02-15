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

class LocationType extends AngularType
{
    public function __construct($ngAlias = 'clfm')
    {
        $this->forApi = false;
        $this->ngAlias = $ngAlias;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'name', 'required' => true))
            ->add('boxNumber', 'text', array('label' => 'box_number', 'required' => false))
            ->add('streetNumber', 'text', array('label' => 'street_number', 'required' => true))
            ->add('street', 'text', array('label' => 'street', 'required' => true))
            ->add('pc', 'text', array('label' => 'postal_code', 'required' => true))
            ->add('town', 'text', array('label' => 'town', 'required' => true))
            ->add('country', 'text', array('label' => 'country', 'required' => true))
            ->add('phone', 'text', array('label' => 'phone', 'required' => false));
    }

    public function getName()
    {
        return 'location_form';
    }

    public function enableApi()
    {
        $this->forApi = true;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $default = array('translation_domain' => 'platform');
        if ($this->forApi) $default['csrf_protection'] = false;
        $default['ng-model'] = 'location';

        $resolver->setDefaults($default);
    }
}
