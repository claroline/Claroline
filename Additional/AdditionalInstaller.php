<?php

namespace Claroline\InstallationBundle\Additional;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\InstallationBundle\Bundle\BundleVersion;

abstract class AdditionalInstaller extends ContainerAware implements AdditionalInstallerInterface
{
    protected $environment;
    private $logger;

    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    public function setLogger(\Closure $logger)
    {
        $this->logger = $logger;
    }

    public function preInstall()
    {
    }

    public function postInstall()
    {
    }

    public function preUpdate($currentVersion, $targetVersion)
    {
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
    }

    public function preUninstall()
    {
    }

    public function postUninstall()
    {
    }

    protected function log($message)
    {
        if ($this->logger) {
            $log = $this->logger;
            $log($message);
        }
    }
}
