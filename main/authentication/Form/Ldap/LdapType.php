<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Form\Ldap;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class LdapType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'name',
                'constraints' => [
                    new NotBlank(),
                    new regex('/^[\w ]*$/'),
                ],
            ]
        )
        ->add(
            'host',
            TextType::class,
            [
                'label' => 'host',
                'constraints' => [new NotBlank()],
            ]
        )
        ->add('port', NumberType::class, ['label' => 'port'])
        ->add('dn', TextType::class, ['label' => 'distinguished_name'])
        ->add('user', TextType::class, ['label' => 'username'])
        ->add('password', PasswordType::class, ['label' => 'password', 'always_empty' => false])
        ->add(
            'protocol_version',
            ChoiceType::class,
            [
                'choices' => ['1' => '1', '2' => '2', '3' => '3'],
                'required' => true,
                'label' => 'protocol_version',
                'data' => 3,
            ]
        );
        $builder->add('append_dn', CheckboxType::class, [
            'label' => 'append_dn',
            'required' => false,
        ]);
        $builder->add('auto_creation', CheckboxType::class, [
            'label' => 'auto_creation',
            'required' => false,
        ]);
        $builder->add('active', CheckboxType::class, [
            'label' => 'active',
            'required' => false,
        ]);
    }

    public function getName()
    {
        return 'platform_parameters_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'ldap']);
    }
}
