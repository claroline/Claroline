<?php

namespace UJM\LtiBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use UJM\LtiBundle\Installation\AdditionalInstaller;

class UJMLtiBundle extends DistributionPluginBundle
{
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
