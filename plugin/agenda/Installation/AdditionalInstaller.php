<?php

namespace Claroline\AgendaBundle\Installation;

use Claroline\AgendaBundle\Installation\Updater\Updater050002;
use Claroline\AgendaBundle\Installation\Updater\Updater120000;
use Claroline\AgendaBundle\Installation\Updater\Updater120545;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class AdditionalInstaller extends BaseInstaller implements ContainerAwareInterface
{
    public function preUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '12.0.0', '<=')) {
            $updater = new Updater120000($this->container);
            $updater->setLogger($this->logger);
            $updater->preUpdate();
        }
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '5.0.1', '<=')) {
            $updater = new Updater050002($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.0.0', '<=')) {
            $updater = new Updater120000($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.5.45', '<=')) {
            $updater = new Updater120545($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
    }
}
