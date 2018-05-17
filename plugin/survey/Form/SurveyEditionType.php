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
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurveyEditionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'description',
            'tinymce'
        );
        $builder->add(
            'startDate',
            'date',
            array(
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            )
        );
        $builder->add(
            'endDate',
            'date',
            array(
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            )
        );
        $builder->add(
            'hasPublicResult',
            CheckboxType::class,
            array('required' => true)
        );
        $builder->add(
            'allowAnswerEdition',
            CheckboxType::class,
            array('required' => true)
        );
    }

    public function getName()
    {
        return 'survey_edition_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'survey'));
    }
}
