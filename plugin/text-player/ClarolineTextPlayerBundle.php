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
        
        return $config;
    }

    public function hasMigrations()
    {
        return false;
    }
}
