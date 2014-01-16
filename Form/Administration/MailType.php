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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('transport', 'choice', array('choices' => array('sendmail' => 'sendmail', 'smtp' => 'smtp', 'gmail' => 'gmail')))
            ->add('host', 'text')
            ->add('username', 'text')
            ->add('password', 'text')
            ->add('auth_mode', 'choice', array('choices' => array('empty_value' => '', 'plain' => 'plain', 'login' => 'login', 'cram-md5' => 'cram-md5')))
            ->add('encryption', 'choice', array('choices' => array('empty_value' => '', 'tls' => 'tls', 'ssl' => 'ssl' )))
            ->add('port', 'number');
    }

    public function getName()
    {
        return 'platform_parameters_form';
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
