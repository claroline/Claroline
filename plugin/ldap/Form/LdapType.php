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
            array(
                'label' => 'name',
                'constraints' => array(
                    new NotBlank(),
                    new regex('/^[\w ]*$/'),
                ),
            )
        )
        ->add(
            'host',
            'text',
            array(
                'label' => 'host',
                'constraints' => array(new NotBlank()),
            )
        )
        ->add('port', 'number', array('label' => 'port'))
        ->add('dn', 'text', array('label' => 'distinguished_name'))
        ->add('user', 'text', array('label' => 'username'))
        ->add('password', 'password', array('label' => 'password'))
        ->add(
            'protocol_version',
            'choice',
            array(
                'choices' => array('1' => '1', '2' => '2', '3' => '3'),
                'required' => true,
                'label' => 'protocol_version',
                'data' => 3,
            )
        );
        $builder->add('append_dn', 'checkbox', array(
            'label' => 'append_dn',
            'required' => false,
        ));
        $builder->add('auto_creation', 'checkbox', array(
            'label' => 'auto_creation',
            'required' => false,
        ));
    }

    public function getName()
    {
        return 'platform_parameters_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'ldap'));
    }
}
