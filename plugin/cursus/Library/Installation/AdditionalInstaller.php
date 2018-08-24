<?php

namespace Claroline\CursusBundle\Library\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class AdditionalInstaller extends BaseInstaller implements ContainerAwareInterface
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        switch (true) {
            case version_compare($currentVersion, '1.0.4', '<'):
                $updater = new Updater\Updater010004($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '10.0.0', '<'):
                $updater = new Updater\Updater100000($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '12.0.0', '<'):
                $updater = new Updater\Updater120000($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
        }
    }
}
