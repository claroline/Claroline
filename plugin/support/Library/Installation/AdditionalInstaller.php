<?php

namespace FormaLibre\SupportBundle\Library\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        switch (true) {
            case version_compare($currentVersion, '10.0.0', '<'):
                $updater = new Updater\Updater100000($this->container, $this->logger);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
        }
    }
}
