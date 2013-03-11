<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class InstallType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'dbHost',
                'text',
                array(
                    'label' => 'Serveur'
                )
            )
            ->add(
                'dbName',
                'text',
                array('label' => 'nom de la base de donnée')
            )
            ->add(
                'dbUser',
                'text',
                array('label' => 'nom d\'utilisateur')
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

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Claroline\CoreBundle\Library\Installation\Install',
            'translation_domain' => 'install'
        );
    }
}