<?php

namespace Icap\SocialmediaBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\CoreBundle\Library\Installation\AdditionalInstaller;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

/**
 * Bundle class.
 * Uncomment if necessary.
 */
class IcapSocialmediaBundle extends DistributionPluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml', null, 'icap_socialmedia');
    }

    /*
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
    */
}
