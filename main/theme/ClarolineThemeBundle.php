<?php

namespace Claroline\ThemeBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

class ClarolineThemeBundle extends DistributionPluginBundle
{
    public function hasMigrations()
    {
        return false;
    }
}
