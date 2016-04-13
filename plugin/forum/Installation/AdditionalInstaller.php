<?php

namespace Claroline\ForumBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function preUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '2.2.0', '<') && version_compare($targetVersion, '2.1.2', '>=')) {
            $updater020200 = new Updater\Updater020200($this->container);
            $updater020200->setLogger($this->logger);
            $updater020200->preUpdate();
        }
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '2.2.0', '<') && version_compare($targetVersion, '2.1.2', '>=')) {
            $updater020200 = new Updater\Updater020200($this->container);
            $updater020200->setLogger($this->logger);
            $updater020200->postUpdate();
        }

        if (version_compare($currentVersion, '2.2.10', '<')) {
            $updater020204 = new Updater\Updater020210($this->container);
            $updater020204->setLogger($this->logger);
            $updater020204->postUpdate();
        }

        if (version_compare($currentVersion, '2.3.0', '<')) {
            $updater020300 = new Updater\Updater020300($this->container);
            $updater020300->setLogger($this->logger);
            $updater020300->postUpdate();
        }

        if (version_compare($currentVersion, '3.1.0', '<')) {
            $updater020300 = new Updater\Updater030100($this->container);
            $updater020300->setLogger($this->logger);
            $updater020300->postUpdate();
        }
    }
}
