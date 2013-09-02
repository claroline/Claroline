<?php

namespace Claroline\InstallationBundle\Bundle;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

interface InstallableInterface extends BundleInterface
{
    public function getPreInstallationAction($version = null);
    public function hasMigrations();
    public function getRequiredFixturesDirectory($version = null);
    public function getOptionalFixturesDirectory($version = null);
}
