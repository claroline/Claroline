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

class ActivityRuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'action',
            'choice',
            array(
                'choices' => array('automatic' => 'evaluation-automatic', 'manual' => 'evaluation-manual'),
                'required' => true
            )
        );
        $builder->add(
            'occurence',
            'integer',
            array('required' => false)
        );
        $builder->add(
            'result',
            'integer',
            array('required' => false)
        );
        $builder->add(
            'result',
            'integer',
            array('required' => false)
        );
        $builder->add(
            'activeFrom',
            'date',
            array(
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd'
            )
        );
        $builder->add(
            'activeUntil',
            'date',
            array(
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd'
            )
        );
    }

    public function getName()
    {
        return 'activity_rule_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array('translation_domain' => 'platform')
        );
    }
}
