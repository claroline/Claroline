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

class LeaveType extends AngularType
{
    public function __construct()
    {
        $this->forApi = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', 'datetime', array('label' => 'start', 'required' => true))
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
        return 'leave_form';
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
        $default['ng-model'] = 'leave';

        $resolver->setDefaults($default);
    }
}
