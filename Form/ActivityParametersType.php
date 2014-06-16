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

class ActivityParametersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'max_duration',
            'integer',
            array('required' => false)
        );
        $builder->add(
            'max_attempts',
            'integer',
            array('required' => false)
        );
        $builder->add(
            'evaluation_type',
            'choice',
            array(
                'choices' => array('automatic' => 'evaluation-automatic', 'manual' => 'evaluation-manual'),
                'required' => false
            )
        );
    }

    public function getName()
    {
        return 'activity_parameters_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array('translation_domain' => 'platform')
        );
    }
}
