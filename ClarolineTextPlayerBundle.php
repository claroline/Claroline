<?php

namespace Claroline\TextPlayerBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

/**
 * Bundle class.
 * Uncomment if necessary.
 */
class ClarolineTextPlayerBundle extends PluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();
        return $config->addRoutingResource(__DIR__ . '/Resources/config/routing.yml', null, 'textplayer');
    }

    public function hasMigrations()
    {
        return false;
    }
}
