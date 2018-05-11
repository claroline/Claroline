<?php

namespace FormaLibre\PresenceBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class AdditionalInstaller extends BaseInstaller implements ContainerAwareInterface
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '6.1.1', '<')) {
            $updater060101 = new Updater\Updater060101($this->container);
            $updater060101->setLogger($this->logger);
            $updater060101->postUpdate();
        }
    }
}
