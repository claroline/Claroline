<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class InstallType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
<<<<<<< HEAD
                ->add('dbHost', 'text', array(
                    'label' => 'Serveur',
                    'attr' => array('placeholder' => 'localhost')))
                ->add('dbName', 'text', array(
                    'label' => 'nom de la base de donnée'))
                ->add('dbUser', 'text', array(
                    'label' => 'nom d\'utilisateur'))
                ->add('dbPassword', 'password', array(
                    'label' => 'mot de passe',
                    'required' => false));
=======
            ->add('dbHost', 'text', array(
                'label' => 'Serveur',
                'attr' => array('value' => 'localhost')))
            ->add('dbName', 'text', array(
                'label' => 'nom de la base de donnée'))
            ->add('dbUser', 'text', array(
                'label' => 'nom d\'utilisateur'))
            ->add('dbPassword', 'password', array(
                'label' => 'mot de passe',
                'required' => false));
>>>>>>> ac932bf4d1d1f3630546aa30a0076df2db67a181
    }

    public function getName()
    {
        return 'install_form';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Claroline\CoreBundle\Entity\Install',
        );
    }
}
