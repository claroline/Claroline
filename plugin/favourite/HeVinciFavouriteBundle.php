<?php

namespace HeVinci\FavouriteBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use HeVinci\FavouriteBundle\Installation\AdditionalInstaller;

class HeVinciFavouriteBundle extends DistributionPluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml', null, 'favourite');
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
