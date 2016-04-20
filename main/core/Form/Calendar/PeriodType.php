<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Calendar;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Claroline\CoreBundle\Form\Angular\AngularType;

class PeriodType extends AngularType
{
    public function __construct()
    {
        $this->forApi = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start', 'datetime', array('label' => 'start', 'required' => true))
            ->add('end', 'datetime', array('label' => 'end', 'required' => true))
            ->add('name', 'text', array('label' => 'name', 'required' => false))
            ->add('description', 'text', array('label' => 'description', 'required' => false))
            ->add('year', 'entity', array(
                    'class' => 'ClarolineCoreBundle:Calendar\Year',
                    'property' => 'name',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true,
                )
            );
    }

    public function getName()
    {
        return 'period_form';
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
        $default['ng-model'] = 'period';

        $resolver->setDefaults($default);
    }
}
