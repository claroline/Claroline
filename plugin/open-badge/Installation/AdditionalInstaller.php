<?php

namespace Claroline\OpenBadgeBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class AdditionalInstaller extends BaseInstaller implements ContainerAwareInterface
{
    public function preUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '12.5.6', '<')) {
            $updater = new Updater\Updater120506($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->preUpdate();
        }
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '12.3.0', '<')) {
            $updater = new Updater\Updater120300($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.5.0', '<')) {
            $updater = new Updater\Updater120500($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
    }
}
