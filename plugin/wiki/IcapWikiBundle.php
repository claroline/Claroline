<?php

namespace Icap\WikiBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Icap\WikiBundle\Installation\AdditionalInstaller;

class IcapWikiBundle extends PluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml', null, 'icap_wiki');
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    public function getPluginsRequirements()
    {
        return ['Icap\\NotificationBundle\\IcapNotificationBundle'];
    }
}
