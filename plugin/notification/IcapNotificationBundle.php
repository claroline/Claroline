<?php

namespace Icap\NotificationBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Icap\NotificationBundle\Installation\AdditionalInstaller;

class IcapNotificationBundle extends DistributionPluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        if (file_exists($routingFile = $this->getPath().'/Resources/config/routing.yml')) {
            $config->addRoutingResource($routingFile, null, 'icap_notification');
        }

        return $config;
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
