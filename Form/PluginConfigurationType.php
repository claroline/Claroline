<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Form;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PluginConfigurationType extends AbstractType
{
    private $configHandler;

    public function __construct(PlatformConfigurationHandler $configHandler)
    {
        $this->configHandler = $configHandler;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $xmppHost = $this->configHandler->getParameter('chat_xmpp_host');
        $configPort = $this->configHandler->getParameter('chat_xmpp_port');
        $xmppPort = empty($configPort) ? 5222 : $configPort;

        $builder->add(
            'host',
            'text',
            array(
                'required' => false,
                'data' => $xmppHost,
                'mapped' => false,
                'label' => 'host'
            )
        );
        $builder->add(
            'port',
            'integer',
            array(
                'required' => false,
                'data' => $xmppPort,
                'mapped' => false,
                'label' => 'port'
            )
        );
    }

    public function getName()
    {
        return 'chat_plugin_configuration_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'chat'));
    }
}
