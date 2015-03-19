<?php

namespace HeVinci\CompetencyBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use HeVinci\CompetencyBundle\Installation\AdditionalInstaller;

class HeVinciCompetencyBundle extends PluginBundle
{
    public function hasMigrations()
    {
        return false;
    }

    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__ . '/Resources/config/routing.yml', null, 'competencies');
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
