<?php

namespace Claroline\TextPlayerBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;

class ClarolineTextPlayerBundle extends DistributionPluginBundle
{
    public function hasMigrations(): bool
    {
        return false;
    }
}
