<?php

namespace Icap\WebsiteBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Icap\WebsiteBundle\Installation\Updater\Updater090300;

class AdditionalInstaller extends BaseInstaller
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '9.3.0', '<=')) {
            $updater = new Updater090300($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
    }
}
