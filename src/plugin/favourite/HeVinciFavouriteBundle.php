<?php

namespace HeVinci\FavouriteBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use HeVinci\FavouriteBundle\Installation\AdditionalInstaller;

class HeVinciFavouriteBundle extends DistributionPluginBundle
{
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
