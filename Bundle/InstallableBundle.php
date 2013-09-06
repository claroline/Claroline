<?php

namespace Claroline\InstallationBundle\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class InstallableBundle extends Bundle implements InstallableInterface
{
    public function hasMigrations()
    {
        return true;
    }

    public function getPreInstallAction($environment)
    {
        return null;
    }

    public function getPostInstallAction($environment)
    {
        return null;
    }

    public function getPreUpdateAction($environment, $version)
    {
        return null;
    }

    public function getPostUpdateAction($environment, $version)
    {
        return null;
    }

    public function getPreUninstallAction($environment)
    {
        return null;
    }

    public function getPostUninstallAction($environment)
    {
        return null;
    }

    public function getRequiredFixturesDirectory($environment)
    {
        return null;
    }

    public function getOptionalFixturesDirectory($environment)
    {
        return null;
    }
}
