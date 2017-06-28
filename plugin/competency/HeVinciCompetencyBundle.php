<?php

namespace HeVinci\CompetencyBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use HeVinci\CompetencyBundle\Installation\AdditionalInstaller;

class HeVinciCompetencyBundle extends DistributionPluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml');
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
