<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InstallType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'dbHost',
                'text'
            )
            ->add(
                'dbName',
                'text'
            )
            ->add(
                'dbUser',
                'text'
            )
            ->add(
                'dbPassword',
                'password',
                array(
                    'label' => 'mot de passe',
                    'required' => false
                )
            )
            ->add(
                'dbDriver',
                'choice',
                array(
                    'choices' => array(
                        'pdo_mysql' => 'pdo_mysql',
                        'pdo_pgsql' => 'pdo_pgsql',
                         'pdo_sqlsrv' => 'pdo_sqlsrv'
                    )
                )
            );
    }

    public function getName()
    {
        return 'install_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver

        ->setDefaults(
            array(
                'class' => 'Claroline\CoreBundle\Library\Installation\Install',
                'translation_domain' => 'install',
                'dbHost' => 'ok',
                'dbName' => '',
                'dbUser' => '',
                'dbDriver' => 'pdo_mysql'
                )
        )
        ->setRequired(
            array(
                'dbHost',
                'dbName',
                'dbUser',
                'dbDriver',
            )
        )
        ->setOptional(
            array(
                'dbPassword'
            )
        )
        ->setAllowedtypes(
            array(
                'dbHost' => 'string',
                'dbName' => 'string',
                'dbUser' => 'string',
                'dbDriver' => 'string',
                'dbPassword' => array('string' ,null)
            )
        )
        ->setAllowedValues(
            array(
                'dbDriver' => array('pdo_mysql', 'pdo_pgsql', 'pdo_sqlsrv'),
            )
        );
    }
}