<?php

namespace Icap\WebsiteBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Icap\WebsiteBundle\Installation\AdditionalInstaller;

class IcapWebsiteBundle extends DistributionPluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml', null, 'icap_website');
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
