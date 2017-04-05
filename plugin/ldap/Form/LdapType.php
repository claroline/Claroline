<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LdapBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class LdapType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
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
            'text',
            [
                'label' => 'host',
                'constraints' => [new NotBlank()],
            ]
        )
        ->add('port', 'number', ['label' => 'port'])
        ->add('dn', 'text', ['label' => 'distinguished_name'])
        ->add('user', 'text', ['label' => 'username'])
        ->add('password', 'password', ['label' => 'password', 'always_empty' => false])
        ->add(
            'protocol_version',
            'choice',
            [
                'choices' => ['1' => '1', '2' => '2', '3' => '3'],
                'required' => true,
                'label' => 'protocol_version',
                'data' => 3,
            ]
        );
        $builder->add('append_dn', 'checkbox', [
            'label' => 'append_dn',
            'required' => false,
        ]);
        $builder->add('auto_creation', 'checkbox', [
            'label' => 'auto_creation',
            'required' => false,
        ]);
        $builder->add('active', 'checkbox', [
            'label' => 'active',
            'required' => false,
        ]);
    }

    public function getName()
    {
        return 'platform_parameters_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'ldap']);
    }
}
