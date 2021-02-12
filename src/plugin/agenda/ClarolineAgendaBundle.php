<?php

namespace Claroline\AgendaBundle;

use Claroline\AgendaBundle\Installation\AdditionalInstaller;
use Claroline\KernelBundle\Bundle\DistributionPluginBundle;

/**
 * Bundle class.
 * Uncomment if necessary.
 */
class ClarolineAgendaBundle extends DistributionPluginBundle
{
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
