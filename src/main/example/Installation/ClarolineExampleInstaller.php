<?php

namespace Claroline\ExampleBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineExampleInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }

    public function hasFixtures(): bool
    {
        return true;
    }
}
