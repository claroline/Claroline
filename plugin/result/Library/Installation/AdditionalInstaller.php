<?php

namespace Claroline\ResultBundle\Library\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class AdditionalInstaller extends BaseInstaller implements ContainerAwareInterface
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '12.0.0', '<')) {
            $updater = new Updater\Updater120000($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
    }
}
