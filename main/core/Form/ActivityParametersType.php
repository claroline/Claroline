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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityParametersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'withTutor',
            ChoiceType::class,
            [
                'choices' => [0 => 'no', 1 => 'yes'],
                'required' => false,
                'label' => 'with_tutor',
            ]
        );
        $builder->add(
            'max_duration',
            IntegerType::class,
            [
                'attr' => ['min' => 1],
                'required' => false,
                'label' => 'max_second_duration',
            ]
        );
        $builder->add(
            'who',
            ChoiceType::class,
            [
                'choices' => [
                    'individual' => 'individual',
                    'collaborative' => 'collaborative',
                    'mixed' => 'mixed',
                ],
                'required' => false,
                'label' => 'method_of_work',
            ]
        );
        $builder->add(
            'where',
            ChoiceType::class,
            [
                'choices' => [
                    'anywhere' => 'anywhere',
                    'classroom' => 'classroom',
                ],
                'required' => false,
                'label' => 'learning_place',
            ]
        );
        $builder->add(
            'max_attempts',
            IntegerType::class,
            [
                'attr' => ['min' => 1],
                'required' => false,
                'label' => 'max_attempts',
            ]
        );
        $builder->add(
            'evaluation_type',
            ChoiceType::class,
            [
                'choices' => [
                    'manual' => 'evaluation-manual',
                    'automatic' => 'evaluation-automatic',
                ],
                'required' => true,
                'label' => 'evaluation_type',
            ]
        );
    }

    public function getName()
    {
        return 'activity_parameters_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            ['translation_domain' => 'platform']
        );
    }
}
