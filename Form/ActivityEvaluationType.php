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

class ActivityEvaluationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $evaluation = $builder->getData();
        $activityParams = $evaluation->getActivityParameters();
        $evaluationType = empty($activityParams->getEvaluationType()) ?
            'manual' :
            $activityParams->getEvaluationType();

        $statusOptions = array(
            'choices' => array(
                'not_attempted' => 'not_attempted',
                'completed' => 'completed',
                'incomplete' => 'incomplete',
                'passed' => 'passed',
                'failed' => 'failed'
            ),
            'required' => false
        );

        if ($evaluationType === 'automatic') {
            $statusOptions['read_only'] = true;
            $statusOptions['disabled'] = true;
        }

        $builder->add(
            'status',
            'choice',
            $statusOptions
        );
        $builder->add(
            'numScore',
            'integer',
            array(
                'read_only' => true,
                'required' => false
            )
        );
        $builder->add(
            'date',
            'datetime',
            array(
                'read_only' => true,
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd H:m:s'
            )
        );
        $builder->add(
            'attemptsCount',
            'integer',
            array(
                'read_only' => true,
                'required' => false
            )
        );
        $builder->add(
            'score',
            'text',
            array('required' => false)
        );
        $builder->add(
            'comment',
            'textarea',
            array(
                'attr' => array('rows' => 5),
                'required' => false
            )
        );
    }

    public function getName()
    {
        return 'activity_evaluation_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array('translation_domain' => 'platform')
        );
    }
}
