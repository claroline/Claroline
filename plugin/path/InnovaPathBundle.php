<?php

namespace Innova\PathBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Innova\PathBundle\Installation\AdditionalInstaller;

/**
 * Bundle class.
 */
class InnovaPathBundle extends DistributionPluginBundle
{
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    public function getRequiredPlugins()
    {
        return [
            'Claroline\\TagBundle\\ClarolineTagBundle',
        ];
    }
}
