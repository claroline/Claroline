<?php

namespace Claroline\TagBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Executes correct action when PathBundle is installed or updated.
 */
class AdditionalInstaller extends BaseInstaller implements ContainerAwareInterface
{
    /**
     * Action to perform after Bundle update
     * Load default allowed types for the non digital resources if the previous bundle version is less than 1.1.
     *
     * @param string $currentVersion - The current version of the bundle
     * @param string $targetVersion  - The version of the bundle which will be installed instead
     *
     * @return AdditionalInstaller
     */
    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '12.2.7', '<')) {
            $updater120000 = new Updater\Updater120207($this->container);
            $updater120000->setLogger($this->logger);
            $updater120000->postUpdate();
        }

        return $this;
    }
}
