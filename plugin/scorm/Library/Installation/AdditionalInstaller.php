<?php

namespace Claroline\ScormBundle\Library\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class AdditionalInstaller extends BaseInstaller implements ContainerAwareInterface
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '10.0.0', '<')) {
            $updater = new Updater\Updater100000($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
    }
}
