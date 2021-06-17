<?php

namespace Claroline\EvaluationBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;

class ClarolineEvaluationBundle extends DistributionPluginBundle
{
    public function hasMigrations(): bool
    {
        return false;
    }
}
