<?php

namespace Claroline\InstallationBundle\Bundle;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

interface InstallableInterface extends BundleInterface
{
    public function getPreInstallationAction($environment, $version = null);
    public function hasMigrations();
    public function getRequiredFixturesDirectory($environment, $version = null);
    public function getOptionalFixturesDirectory($environment, $version = null);
}
