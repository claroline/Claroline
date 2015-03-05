<?php
namespace Icap\BadgeBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Icap\BadgeBundle\Installation\Updater\Updater040100;

class AdditionalInstaller extends BaseInstaller
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '4.0.0', '<')) {
            $updater040100 = new Updater040100($this->container->get('doctrine.orm.entity_manager'), $this->container->get('doctrine.dbal.default_connection'));
            $updater040100->setLogger($this->logger);
            $updater040100->postUpdate();
        }
    }
}