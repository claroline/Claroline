<?php

namespace HeVinci\FavouriteBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\ForumBundle\Installation\AdditionalInstaller;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

class HeVinciFavouriteBundle extends PluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__ . '/Resources/config/routing.yml', null, 'favourite');
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}