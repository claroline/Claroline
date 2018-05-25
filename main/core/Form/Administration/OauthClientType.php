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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class OauthClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            ['constraints' => new NotBlank(), 'label' => 'name']
        );
        $builder->add(
            'allowed_grant_types',
            ChoiceType::class,
            [
                'choices' => [
                    'authorization_code' => '_authorization_code',
                    'password' => '_password',
                    'refresh_token' => '_refresh_token',
                    'token' => '_token',
                    'client_credentials' => '_client_credentials',
                ],
                'disabled' => isset($this->lockedParams['mailer_transport']),
                'label' => 'grant_type',
                'multiple' => true,
                'constraints' => new NotBlank(),
                'expanded' => true,
            ]
        );
        $builder->add(
            'uri',
            TextType::class,
            ['label' => 'uri']
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
