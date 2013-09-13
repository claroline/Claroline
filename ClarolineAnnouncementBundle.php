<?php

namespace Claroline\AnnouncementBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

/**
 * Bundle class.
 */
class ClarolineAnnouncementBundle extends PluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__ . '/Resources/config/routing.yml', null, 'announcement');
    }

    public function getRequiredFixturesDirectory($environment)
    {
        return 'DataFixtures';
    }
}