<?php

namespace Claroline\InstallationBundle\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class InstallableBundle extends Bundle implements InstallableInterface
{
    public function hasMigrations()
    {
        return true;
    }

    public function getRequiredFixturesDirectory($environment)
    {
        return null;
    }

    public function getOptionalFixturesDirectory($environment)
    {
        return null;
    }

    public function getAdditionalInstaller()
    {
        return null;
    }
}
