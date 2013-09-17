<?php

namespace ICAP\BlogBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

class IcapBlogBundle extends PluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__ . '/Resources/config/routing.yml', null, 'icap_blog');
    }

    public function getRequiredFixturesDirectory($environment)
    {
        return 'DataFixtures';
    }
}
