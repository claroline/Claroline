<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'title',
            'text'
        );
        $builder->add(
            'question',
            'tinymce'
        );
        $builder->add(
            'type',
            'choice',
            [
                'choices' => [
                    'simple_text' => 'simple_text',
                    'open_ended' => 'open_ended',
                    'open_ended_bare' => 'open_ended_bare',
                    'multiple_choice_single' => 'multiple_choice_single_answer',
                    'multiple_choice_multiple' => 'multiple_choice_multiple_answers',
                ],
                'required' => true,
            ]
        );
        $builder->add(
            'commentAllowed',
            'checkbox',
            ['required' => true]
        );
        $builder->add(
            'commentLabel',
            'text',
            ['required' => false]
        );
    }

    public function getName()
    {
        return 'question_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'survey']);
    }
}
