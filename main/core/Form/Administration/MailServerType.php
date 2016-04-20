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

class MailServerType extends AbstractType
{
    private $formDisplay;
    private $transport;
    private $lockedParams;

    public function __construct($transport, array $lockedParams = array())
    {
        $this->transport = $transport;
        $this->formDisplay = array(
            'sendmail' => array(
                'host' => false,
                'username' => false,
                'password' => false,
                'auth_mode' => false,
                'encryption' => false,
                'port' => false,
            ),
            'gmail' => array(
                'host' => false,
                'username' => true,
                'password' => true,
                'auth_mode' => false,
                'encryption' => false,
                'port' => false,
            ),
            'smtp' => array(
                'host' => true,
                'username' => true,
                'password' => true,
                'auth_mode' => true,
                'encryption' => true,
                'port' => true,
            ),
        );
        $this->lockedParams = $lockedParams;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'mailer_transport',
                'choice',
                array(
                    'choices' => array('sendmail' => 'sendmail', 'smtp' => 'smtp', 'gmail' => 'gmail'),
                    'disabled' => isset($this->lockedParams['mailer_transport']),
                    'label' => 'transport',
                )
            )
            ->add(
                'mailer_host',
                'text',
                array(
                    'required' => false,
                    'theme_options' => array('display_row' => $this->formDisplay[$this->transport]['host']),
                    'disabled' => isset($this->lockedParams['mailer_host']),
                    'label' => 'host',
                )
            )
            ->add(
                'mailer_username',
                'text',
                array(
                    'required' => false,
                    'theme_options' => array('display_row' => $this->formDisplay[$this->transport]['username']),
                    'disabled' => isset($this->lockedParams['mailer_username']),
                    'label' => 'username',
                )
            )
            ->add(
                'mailer_password',
                'password',
                array(
                    'required' => false,
                    'theme_options' => array('display_row' => $this->formDisplay[$this->transport]['password']),
                    'disabled' => isset($this->lockedParams['mailer_password']),
                    'label' => 'password',
                )
            )
            ->add(
                'mailer_auth_mode',
                'choice',
                array(
                    'choices' => array(null => '', 'plain' => 'plain', 'login' => 'login', 'cram-md5' => 'cram-md5'),
                    'required' => false,
                    'theme_options' => array('display_row' => $this->formDisplay[$this->transport]['auth_mode']),
                    'disabled' => isset($this->lockedParams['mailer_auth_mode']),
                    'label' => 'auth_mode',
                )
            )
            ->add(
                'mailer_encryption',
                'choice',
                array(
                    'choices' => array(null => '', 'tls' => 'tls', 'ssl' => 'ssl'),
                    'required' => false,
                    'theme_options' => array('display_row' => $this->formDisplay[$this->transport]['encryption']),
                    'disabled' => isset($this->lockedParams['mailer_encryption']),
                    'label' => 'encryption',
                )
            )
            ->add(
                'mailer_port',
                'number',
                array(
                    'required' => false,
                    'theme_options' => array('display_row' => $this->formDisplay[$this->transport]['port']),
                    'disabled' => isset($this->lockedParams['mailer_port']),
                    'label' => 'port',
                )
            );
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
