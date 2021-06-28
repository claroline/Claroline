<?php

namespace Icap\FormulaPluginBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;

/**
 * Formula Plugin bundle class.
 */
class IcapFormulaPluginBundle extends DistributionPluginBundle
{
    public function hasMigrations(): bool
    {
        return false;
    }
}
