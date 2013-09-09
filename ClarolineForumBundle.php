<?php

namespace Claroline\ForumBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

/**
 * Bundle class.
 */
class ClarolineForumBundle extends PluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource($routingFile, null, 'forum');
    }

    public function getRequiredFixturesDirectory($environment)
    {
        return __DIR__ . '/DataFixtures';
    }
}
