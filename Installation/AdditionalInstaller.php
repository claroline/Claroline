<?php

namespace UJM\ExoBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function preUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '6.0.0', '<=')) {
            $updater = new Updater600100($this->container);
            $updater->setLogger($this->logger);
            $updater->preUpdate();
        }

        if (version_compare($currentVersion, '6.2.0', '<=')) {
            $updater = new Updater600200($this->container);
            $updater->setLogger($this->logger);
            $updater->preUpdate();
        }
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '6.0.0', '<=')) {
            $updater = new Updater600100($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '6.2.0', '<=')) {
            $updater = new Updater600200($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
    }

}
