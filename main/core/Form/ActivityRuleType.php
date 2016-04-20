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

use Claroline\CoreBundle\Manager\ActivityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ActivityRuleType extends AbstractType
{
    private $activityManager;
    private $translator;

    public function __construct(
        ActivityManager $activityManager,
        TranslatorInterface $translator
    ) {
        $this->activityManager = $activityManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ruleActions = $this->activityManager->getAllDistinctActivityRuleActions();
        $actions = array('none' => 'none');

        foreach ($ruleActions as $ruleAction) {
            $actions[$ruleAction['action']] = $this->translator->trans(
                'log_'.$ruleAction['action'].'_filter',
                array(),
                'log'
            );
        }

        $builder->add(
            'action',
            'choice',
            array(
                'choices' => $actions,
                'required' => true,
                'label' => 'action',
            )
        );
        $builder->add(
            'occurrence',
            'integer',
            array(
                'attr' => array('min' => 1),
                'required' => true,
                'label' => 'occurence',
            )
        );
        $builder->add(
            'result',
            'integer',
            array(
                'attr' => array('min' => 0),
                'required' => false,
                'label' => 'result',
            )
        );
        $builder->add(
            'resultMax',
            'integer',
            array(
                'label' => '/',
                'attr' => array('min' => 1),
                'required' => false,
            )
        );
        $builder->add(
            'isResultVisible',
            'checkbox',
            array('required' => false)
        );
        $builder->add(
            'activeFrom',
            'date',
            array(
                'attr' => array('class' => 'activity-rule-option-date'),
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            )
        );
        $builder->add(
            'activeUntil',
            'date',
            array(
                'attr' => array('class' => 'activity-rule-option-date'),
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
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
