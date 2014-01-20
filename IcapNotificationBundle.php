<?php

namespace Icap\NotificationBundle;

use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\InstallationBundle\Bundle\InstallableBundle;

class IcapNotificationBundle extends InstallableBundle implements AutoConfigurableInterface
{

    public function supports($environment)
    {
        return true;
    }

    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        if (file_exists($routingFile = $this->getPath() . '/Resources/config/routing.yml')) {
            $config->addRoutingResource($routingFile, null, 'icap_notification');
        }

        if (file_exists($containerFile = $this->getPath() . '/Resources/config/config.yml')) {
            $config->addContainerResource($containerFile);
        }

        return $config;
    }
}
