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
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['reset']) {
            $builder->add('password', PasswordType::class);
        }

        $builder->add(
            'plainPassword',
            RepeatedType::class,
            [
                'type' => PasswordType::class,
                'invalid_message' => 'password_mismatch',
                'first_options' => ['label' => 'new_password'],
                'second_options' => ['label' => 'repeat_password'],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform', 'reset' => false]);
    }
}
