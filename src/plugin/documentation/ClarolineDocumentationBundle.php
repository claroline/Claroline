<?php

namespace Claroline\DocumentationBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;

class ClarolineDocumentationBundle extends DistributionPluginBundle
{
    public function hasMigrations(): bool
    {
        return false;
    }
}
