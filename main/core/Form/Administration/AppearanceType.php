<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Administration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppearanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name_active',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'show_name_in_top_bar',
                    'disabled' => isset($options['lockedParams']['nameActive']),
                ]
            )
            ->add(
                'footer',
                TextType::class,
                [
                    'required' => false,
                    'disabled' => isset($options['lockedParams']['footer']),
                    'label' => 'footer',
                ]
            )
            ->add(
                'theme',
                ChoiceType::class,
                [
                    'choices' => $options['themes'],
                    'disabled' => isset($options['lockedParams']['theme']),
                    'label' => 'theme',
                ]
            )
            ->add(
                'resource_icon_set',
                ChoiceType::class,
                [
                    'choices' => $options['icons'],
                    'disabled' => isset($options['lockedParams']['resource_icon_set']),
                    'label' => 'resource_icon_set',
                ]
            );
    }

    public function getName()
    {
        return 'platform_parameters_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
          'translation_domain' => 'platform',
          'themes' => [],
          'icons' => [],
          'lockedParams' => [],
        ]);
    }
}
