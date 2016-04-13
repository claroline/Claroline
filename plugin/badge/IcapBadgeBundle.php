<?php

namespace Icap\BadgeBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Icap\BadgeBundle\Installation\AdditionalInstaller;

class IcapBadgeBundle extends PluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        $config
            ->addRoutingResource($this->getPath().'/Resources/config/routing.yml')
            ->addContainerResource($this->getPath().'/Resources/config/idci_exporter.yml')
            ->addContainerResource($this->getPath().'/Resources/config/doctrine.yml')
            ->addContainerResource($this->getPath().'/Resources/config/twig.yml')
        ;

        return $config;
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
