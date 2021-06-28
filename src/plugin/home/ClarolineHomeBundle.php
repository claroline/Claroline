<?php

namespace Claroline\HomeBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;

class ClarolineHomeBundle extends DistributionPluginBundle
{
    public function getPostInstallFixturesDirectory(string $environment): string
    {
        return 'DataFixtures/PostInstall';
    }
}
