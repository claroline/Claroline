<?php

namespace Claroline\CursusBundle\Library\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        switch (true) {

            case version_compare($currentVersion, '1.0.4', '<'):
                $updater = new Updater\Updater010004($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
        }
    }
}