<?php

namespace HeVinci\FavouriteBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class HeVinciFavouriteInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}
