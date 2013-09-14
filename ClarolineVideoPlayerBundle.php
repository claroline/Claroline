<?php

namespace Claroline\VideoPlayerBundle;

use Claroline\CoreBundle\Library\PluginBundle;

class ClarolineVideoPlayerBundle extends PluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__ . '/Resources/config/routing.yml', null, 'video-player');
    }

    public function hasMigrations()
    {
        return false;
    }
}

