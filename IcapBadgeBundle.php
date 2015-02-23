<?php

namespace Icap\BadgeBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Icap\BlogBundle\Installation\AdditionalInstaller;

class IcapBadgeBundle extends PluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        $config
            ->addRoutingResource($this->getPath() . '/Resources/config/routing.yml')
            ->addRoutingResource($this->getPath() . '/Resources/config/idci_exporter.yml')
            ->addRoutingResource($this->getPath() . '/Resources/config/twig.yml');

        return $config;
    }
}
