<?php

namespace Claroline\AgendaBundle\Installation;

use Claroline\AgendaBundle\Installation\Updater\Updater500002;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function preInstall()
    {
        $updater = new Updater\MigrationUpdater($this->container);
        $updater->setLogger($this->logger);
        $updater->preInstall();
    }

    public function postInstall()
    {
        $updater = new Updater\MigrationUpdater($this->container);
        $updater->setLogger($this->logger);
        $updater->postInstall();
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '5.0.1', '<=')) {
            $updater = new Updater500002($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
    }
}
