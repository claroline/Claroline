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

class LocationType extends AbstractType
{
    public function __construct()
    {
        $this->forApi = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'name', 'required' => true, 'attr' => array('ng-model' => 'newLocation.name')))
            ->add('street', 'text', array('label' => 'street', 'required' => true, 'attr' => array('ng-model' => 'newLocation.street')))
            ->add('streetNumber', 'text', array('label' => 'street_number', 'required' => true, 'attr' => array('ng-model' => 'newLocation.streetNumber')))
            ->add('boxNumber', 'text', array('label' => 'box_number', 'required' => true, 'attr' => array('ng-model' => 'newLocation.boxNumber')))
            ->add('pc', 'text', array('label' => 'postal_code', 'required' => true, 'attr' => array('ng-model' => 'newLocation.pc')))
            ->add('town', 'text', array('label' => 'town', 'required' => true, 'attr' => array('ng-model' => 'newLocation.town')))
            ->add('country', 'text', array('label' => 'country', 'required' => true, 'attr' => array('ng-model' => 'newLocation.country')))
            ->add('phone', 'text', array('label' => 'phone', 'required' => true, 'attr' => array('ng-model' => 'newLocation.phone')));
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

        $resolver->setDefaults($default);
    }
}
