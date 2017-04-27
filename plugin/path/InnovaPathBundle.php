<?php

namespace Innova\PathBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Innova\PathBundle\Installation\AdditionalInstaller;

/**
 * Bundle class.
 */
class InnovaPathBundle extends PluginBundle implements AutoConfigurableInterface
{
    public function supports($environment)
    {
        return true;
    }

    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml');
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    public function getRequiredPlugins()
    {
        return [
            'Claroline\\TagBundle\\ClarolineTagBundle',
        ];
    }
}
