<?php

namespace Icap\FormulaPluginBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

/**
 * Formula Plugin bundle class.
 */
class IcapFormulaPluginBundle extends DistributionPluginBundle
{
    public function hasMigrations()
    {
        return false;
    }
}
