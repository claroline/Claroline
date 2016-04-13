<?php

namespace Icap\WikiBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function preUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '2.0', '<') && version_compare($currentVersion, '1.0', '>=') && version_compare($targetVersion, '2.0', '>=')) {
            $updater020000 = new Updater\Updater020000($this->container);
            $updater020000->setLogger($this->logger);
            $updater020000->preUpdate();
        }
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '2.0', '<') && version_compare($currentVersion, '1.0', '>=') && version_compare($targetVersion, '2.0', '>=')) {
            $updater020000 = new Updater\Updater020000($this->container);
            $updater020000->setLogger($this->logger);
            $updater020000->postUpdate();
        }
    }
}
