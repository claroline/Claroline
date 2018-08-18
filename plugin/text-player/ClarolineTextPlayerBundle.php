<?php

namespace Claroline\TextPlayerBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

class ClarolineTextPlayerBundle extends DistributionPluginBundle
{
    public function hasMigrations()
    {
        return false;
    }
}
