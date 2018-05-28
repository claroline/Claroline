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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

//TODO FORM
class ActivityEvaluationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $evaluation = $builder->getData();
        $activityParams = $evaluation->getActivityParameters();
        $type = $activityParams->getEvaluationType();
        $evaluationType = empty($type) ?
            'manual' :
            $activityParams->getEvaluationType();

        $statusOptions = [
            'choices' => [
                'not_attempted' => 'not_attempted',
                'completed' => 'completed',
                'incomplete' => 'incomplete',
                'passed' => 'passed',
                'failed' => 'failed',
            ],
            'required' => false,
            'label' => 'status',
        ];

        if ('automatic' === $evaluationType) {
            $statusOptions['read_only'] = true;
            $statusOptions['disabled'] = true;
        }

        $builder->add(
            'status',
            ChoiceType::class,
            $statusOptions
        );
        $builder->add(
            'numScore',
            IntegerType::class,
            [
                'read_only' => true,
                'required' => false,
                'label' => 'best_score',
            ]
        );
        $builder->add(
            'date',
            'datetime',
            [
                'read_only' => true,
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd H:m:s',
                'label' => 'last_attempt_date',
            ]
        );
        $builder->add(
            'attemptsCount',
            IntegerType::class,
            [
                'read_only' => true,
                'required' => false,
                'label' => 'max_attempts',
            ]
        );
        $builder->add(
            'score',
            TextType::class,
            [
                'required' => false,
                'label' => 'evaluation',
            ]
        );
        $builder->add(
            'comment',
            TextareaType::class,
            [
                'attr' => ['rows' => 5],
                'required' => false,
                'label' => 'comment',
            ]
        );
    }

    public function getName()
    {
        return 'activity_evaluation_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            ['translation_domain' => 'platform']
        );
    }
}
