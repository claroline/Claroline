<?php

namespace Icap\BadgeBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Icap\BadgeBundle\Installation\AdditionalInstaller;

class IcapBadgeBundle extends DistributionPluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config
            ->addRoutingResource($this->getPath().'/Resources/config/routing.yml')
            ->addContainerResource($this->getPath().'/Resources/config/idci_exporter.yml')
            ->addContainerResource($this->getPath().'/Resources/config/doctrine.yml')
            ->addContainerResource($this->getPath().'/Resources/config/twig.yml');
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    public function getRequiredPlugins()
    {
        return [
            'Icap\\PortfolioBundle\\IcapPortfolioBundle',
            'Icap\\NotificationBundle\\IcapNotificationBundle',
        ];
    }
}
