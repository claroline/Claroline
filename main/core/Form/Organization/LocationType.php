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

use Claroline\CoreBundle\Form\Angular\AngularType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
            ->add('name', 'text', ['label' => 'name', 'required' => true])
            ->add('boxNumber', 'text', ['label' => 'box_number', 'required' => false])
            ->add('streetNumber', 'text', ['label' => 'street_number', 'required' => true])
            ->add('street', 'text', ['label' => 'street', 'required' => true])
            ->add('pc', 'text', ['label' => 'postal_code', 'required' => true])
            ->add('town', 'text', ['label' => 'town', 'required' => true])
            ->add('country', 'text', ['label' => 'country', 'required' => true])
            ->add('phone', 'text', ['label' => 'phone', 'required' => false])
            ->add('coordinates', 'text', ['label' => 'coordinates', 'required' => false, 'mapped' => false]);
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
        $default = ['translation_domain' => 'platform'];
        if ($this->forApi) {
            $default['csrf_protection'] = false;
        }
        $default['ng-model'] = 'location';
        $default['ng-controllerAs'] = $this->ngAlias;

        $resolver->setDefaults($default);
    }
}
