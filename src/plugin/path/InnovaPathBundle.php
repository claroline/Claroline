<?php

namespace Innova\PathBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Innova\PathBundle\Installation\AdditionalInstaller;

/**
 * Bundle class.
 */
class InnovaPathBundle extends DistributionPluginBundle
{
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
