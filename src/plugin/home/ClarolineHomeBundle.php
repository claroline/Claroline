<?php

namespace Claroline\HomeBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

class ClarolineHomeBundle extends DistributionPluginBundle
{
    public function getPostInstallFixturesDirectory($environment)
    {
        return 'DataFixtures/PostInstall';
    }
}
