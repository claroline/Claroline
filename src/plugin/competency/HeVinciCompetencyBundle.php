<?php

namespace HeVinci\CompetencyBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use HeVinci\CompetencyBundle\Installation\AdditionalInstaller;

class HeVinciCompetencyBundle extends DistributionPluginBundle
{
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
