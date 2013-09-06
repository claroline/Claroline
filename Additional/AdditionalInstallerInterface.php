<?php

namespace Claroline\InstallationBundle\Additional;

interface AdditionalInstallerInterface
{
    public function setEnvironment($environment);
    public function setLogger(\Closure $logger);
    public function preInstall();
    public function postInstall();
    public function preUpdate($version);
    public function postUpdate($version);
    public function preUninstall();
    public function postUninstall();
}
