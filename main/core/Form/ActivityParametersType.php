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
            'withTutor',
            'choice',
            array(
                'choices' => array(0 => 'no', 1 => 'yes'),
                'required' => false,
                'label' => 'with_tutor',
            )
        );
        $builder->add(
            'max_duration',
            'integer',
            array(
                'attr' => array('min' => 1),
                'required' => false,
                'label' => 'max_second_duration',
            )
        );
        $builder->add(
            'who',
            'choice',
            array(
                'choices' => array(
                    'individual' => 'individual',
                    'collaborative' => 'collaborative',
                    'mixed' => 'mixed',
                ),
                'required' => false,
                'label' => 'method_of_work',
            )
        );
        $builder->add(
            'where',
            'choice',
            array(
                'choices' => array(
                    'anywhere' => 'anywhere',
                    'classroom' => 'classroom',
                ),
                'required' => false,
                'label' => 'learning_place',
            )
        );
        $builder->add(
            'max_attempts',
            'integer',
            array(
                'attr' => array('min' => 1),
                'required' => false,
                'label' => 'max_attempts',
            )
        );
        $builder->add(
            'evaluation_type',
            'choice',
            array(
                'choices' => array(
                    'manual' => 'evaluation-manual',
                    'automatic' => 'evaluation-automatic',
                ),
                'required' => true,
                'label' => 'evaluation_type',
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
