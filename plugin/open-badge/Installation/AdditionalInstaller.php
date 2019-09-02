<?php

namespace Claroline\OpenBadgeBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Claroline\OpenBadgeBundle\Installation\Updater\Updater120300;
use Claroline\OpenBadgeBundle\Installation\Updater\Updater120500;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class AdditionalInstaller extends BaseInstaller implements ContainerAwareInterface
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '12.3.0', '<')) {
            $updater = new Updater120300($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '12.5.0', '<')) {
            $updater = new Updater120500($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
    }
}
