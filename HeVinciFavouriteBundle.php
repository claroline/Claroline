<?php

namespace HeVinci\FavouriteBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use HeVinci\FavouriteBundle\Installation\AdditionalInstaller;

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