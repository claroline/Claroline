<?php

namespace UJM\LtiBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use UJM\LtiBundle\Library\Installation\AdditionalInstaller;

class UJMLtiBundle extends DistributionPluginBundle
{
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    public function hasMigrations()
    {
        return true;
    }
}
