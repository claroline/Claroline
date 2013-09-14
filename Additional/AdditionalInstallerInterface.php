<?php

namespace Claroline\InstallationBundle\Additional;

use Claroline\InstallationBundle\Bundle\BundleVersion;

interface AdditionalInstallerInterface
{
    public function setEnvironment($environment);
    public function setLogger(\Closure $logger);
    public function preInstall();
    public function postInstall();
    public function preUpdate(BundleVersion $current, BundleVersion $target);
    public function postUpdate(BundleVersion $current, BundleVersion $target);
    public function preUninstall();
    public function postUninstall();
}
