<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Form;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * @DI\Service("claroline_form_result")
 * @DI\Tag("form.type")
 */
class ResultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'label' => 'title',
                'constraints' => new NotBlank(),
                'attr' => ['autofocus' => true],
            ])
            ->add('total', 'integer', [
                'label' => 'maximum_mark',
                'translation_domain' => 'results',
                'constraints' => [new NotBlank(), new Range(['min' => 1])],
                'attr' => ['min' => 1],
                'data' => 20,
            ]);
    }

    public function getName()
    {
        return 'claroline_form_result';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('translation_domain', 'platform');
    }
}
