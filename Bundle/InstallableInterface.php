<?php

namespace Claroline\InstallationBundle\Bundle;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

interface InstallableInterface extends BundleInterface
{
    public function hasMigrations();
    public function getPreInstallAction($environment);
    public function getPostInstallAction($environment);
    public function getPreUpdateAction($environment, $version);
    public function getPostUpdateAction($environment, $version);
    public function getPreUninstallAction($environment);
    public function getPostUninstallAction($environment);
    public function getRequiredFixturesDirectory($environment);
    public function getOptionalFixturesDirectory($environment);
}
