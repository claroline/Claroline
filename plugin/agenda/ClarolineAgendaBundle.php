<?php

namespace Claroline\AgendaBundle;

use Claroline\AgendaBundle\Installation\AdditionalInstaller;
use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

/**
 * Bundle class.
 * Uncomment if necessary.
 */
class ClarolineAgendaBundle extends DistributionPluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml', null, 'agenda');
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
