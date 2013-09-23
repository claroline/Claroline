<?php

namespace UJM\ExoBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

class UJMExoBundle extends PluginBundle
{

    public function getContainerExtension()
    {
        return new DependencyInjection\UJMExoExtension();
    }

    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__ . '/Resources/config/routing.yml', null, 'exercise');
    }

    public function getRequiredFixturesDirectory($environment)
    {
        return 'DataFixtures';
    }
}