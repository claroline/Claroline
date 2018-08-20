<?php

namespace UJM\LtiBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use UJM\LtiBundle\Library\Installation\AdditionalInstaller;

/**
 * Bundle class.
 * Uncomment if necessary.
 */
class UJMLtiBundle extends DistributionPluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml', null, 'lti');
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    public function hasMigrations()
    {
        return true;
    }
}
