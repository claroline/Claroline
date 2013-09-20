<?php

namespace UJM\ExoBundle;

use Claroline\CoreBundle\Library\PluginBundle;

class UJMExoBundle extends PluginBundle
{
    public function getRoutingPrefix()
    {
        return 'exercise';
    }

    public function getContainerExtension()
    {
        return new DependencyInjection\UJMExoExtension();
    }
    
    public function getRequiredFixturesDirectory()
    {
        return 'DataFixtures';
    }
}