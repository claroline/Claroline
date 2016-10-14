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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ThemeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'label' => 'name',
                'constraints' => [new NotBlank()],
            ])
            ->add('stylesheet', 'file', [
                'label' => 'stylesheet',
                'translation_domain' => 'theme',
                'constraints' => [
                    new NotBlank(),
                    new File(['maxSize' => '300k']),
                ],
            ])
            ->add(
                'extendingDefault',
                'checkbox',
                [
                    'label' => 'extend_default_theme',
                    'translation_domain' => 'theme',
                    'required' => false,
                ]
            );
    }

    public function getName()
    {
        return 'theme_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
