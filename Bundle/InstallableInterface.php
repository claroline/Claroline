<?php

namespace Claroline\InstallationBundle\Bundle;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

interface InstallableInterface extends BundleInterface
{
    public function hasMigrations();
    public function getRequiredFixturesDirectory($environment);
    public function getOptionalFixturesDirectory($environment);
    public function getAdditionalInstaller();
}
