<?php

namespace Claroline\CommunityBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Claroline\ThemeBundle\ClarolineThemeBundle;

class ClarolineCommunityBundle extends DistributionPluginBundle
{
    public function getRequiredPlugins(): array
    {
        return [
            // this is required to inject community icons in the default set
            ClarolineThemeBundle::class,
        ];
    }
}
