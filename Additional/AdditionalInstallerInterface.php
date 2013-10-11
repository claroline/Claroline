<?php

namespace Claroline\InstallationBundle\Additional;

use Claroline\InstallationBundle\Bundle\BundleVersion;

interface AdditionalInstallerInterface
{
    public function setEnvironment($environment);
    public function setLogger(\Closure $logger);
    public function preInstall();
    public function postInstall();
    public function preUpdate($currentVersion, $targetVersion);
    public function postUpdate($currentVersion, $targetVersion);
    public function preUninstall();
    public function postUninstall();
}
