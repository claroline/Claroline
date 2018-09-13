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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailServerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'mailer_transport',
                ChoiceType::class,
                [
                    'choices' => [
                      'sendmail' => 'sendmail',
                      'smtp' => 'smtp',
                      'gmail' => 'gmail',
                      'postal' => 'postal',
                    ],
                    'disabled' => isset($options['lockedParams']['mailer_transport']),
                    'label' => 'transport',
                ]
            )
            ->add(
                'mailer_host',
                TextType::class,
                [
                    'required' => false,
                    'attr' => ['display_row' => $options['formDisplay'][$options['transport']]['host']],
                    'disabled' => isset($options['lockedParams']['mailer_host']),
                    'label' => 'host',
                ]
            )
            ->add(
                'mailer_username',
                TextType::class,
                [
                    'required' => false,
                    'attr' => ['display_row' => $options['formDisplay'][$options['transport']]['username']],
                    'disabled' => isset($options['lockedParams']['mailer_username']),
                    'label' => 'username',
                ]
            )
            ->add(
                'mailer_password',
                PasswordType::class,
                [
                    'required' => false,
                    'attr' => ['display_row' => $options['formDisplay'][$options['transport']]['password']],
                    'disabled' => isset($options['lockedParams']['mailer_password']),
                    'label' => 'password',
                ]
            )
            ->add(
                'mailer_auth_mode',
                ChoiceType::class,
                [
                    'choices' => [null => '', 'plain' => 'plain', 'login' => 'login', 'cram-md5' => 'cram-md5'],
                    'required' => false,
                    'attr' => ['display_row' => $options['formDisplay'][$options['transport']]['auth_mode']],
                    'disabled' => isset($options['lockedParams']['mailer_auth_mode']),
                    'label' => 'auth_mode',
                ]
            )
            ->add(
                'mailer_encryption',
                ChoiceType::class,
                [
                    'choices' => [null => '', 'tls' => 'tls', 'ssl' => 'ssl'],
                    'required' => false,
                    'attr' => ['display_row' => $options['formDisplay'][$options['transport']]['encryption']],
                    'disabled' => isset($options['lockedParams']['mailer_encryption']),
                    'label' => 'encryption',
                ]
            )
            ->add(
                'mailer_port',
                NumberType::class,
                [
                    'required' => false,
                    'attr' => ['display_row' => $options['formDisplay'][$options['transport']]['port']],
                    'disabled' => isset($options['lockedParams']['mailer_port']),
                    'label' => 'port',
                ]
            )
            ->add(
                'mailer_api_key',
                TextType::class,
                [
                    'required' => false,
                    'attr' => ['display_row' => $options['formDisplay'][$options['transport']]['api_key']],
                    'disabled' => isset($options['lockedParams']['mailer_api_key']),
                    'label' => 'api_key',
                ]
            )
            ->add(
                'mailer_tag',
                TextType::class,
                [
                    'required' => false,
                    'attr' => ['display_row' => $options['formDisplay'][$options['transport']]['tag']],
                    'disabled' => isset($options['lockedParams']['mailer_tag']),
                    'label' => 'tag',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'platform',
            'transport' => 'sendmail',
            'lockedParams' => [],
            'formDisplay' => [
                'sendmail' => [
                    'host' => false,
                    'username' => false,
                    'password' => false,
                    'auth_mode' => false,
                    'encryption' => false,
                    'port' => false,
                    'api_key' => false,
                    'tag' => false,
                ],
                'gmail' => [
                    'host' => false,
                    'username' => true,
                    'password' => true,
                    'auth_mode' => false,
                    'encryption' => false,
                    'port' => false,
                    'api_key' => false,
                    'tag' => false,
                ],
                'smtp' => [
                    'host' => true,
                    'username' => true,
                    'password' => true,
                    'auth_mode' => true,
                    'encryption' => true,
                    'port' => true,
                    'api_key' => false,
                    'tag' => false,
                ],
                'postal' => [
                    'host' => true,
                    'username' => false,
                    'password' => false,
                    'auth_mode' => false,
                    'encryption' => false,
                    'port' => false,
                    'api_key' => true,
                    'tag' => true,
                ],
            ],
        ]);
    }
}
