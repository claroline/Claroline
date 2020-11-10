<?php

namespace Claroline\HomeBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

class ClarolineHomeBundle extends DistributionPluginBundle
{
    public function hasMigrations()
    {
        return false;
    }

    public function getPostInstallFixturesDirectory($environment)
    {
        return 'DataFixtures/PostInstall';
    }
}
