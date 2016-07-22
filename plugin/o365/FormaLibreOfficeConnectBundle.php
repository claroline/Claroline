<?php

namespace FormaLibre\OfficeConnectBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

/**
 * Bundle class.
 */
class FormaLibreOfficeConnectBundle extends PluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml', null, 'officeconnect');
    }

    public function hasMigrations()
    {
        return false;
    }
}
