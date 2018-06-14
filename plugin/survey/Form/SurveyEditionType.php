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

use Claroline\CoreBundle\Form\Field\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurveyEditionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'description',
            TinymceType::class
        );
        $builder->add(
            'startDate',
            DateType::class,
            [
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ]
        );
        $builder->add(
            'endDate',
            DateType::class,
            [
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ]
        );
        $builder->add(
            'hasPublicResult',
            CheckboxType::class,
            ['required' => true]
        );
        $builder->add(
            'allowAnswerEdition',
            CheckboxType::class,
            ['required' => true]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'survey']);
    }
}
