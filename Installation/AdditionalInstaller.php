<?php
namespace Icap\BadgeBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function preInstall()
    {
        $updater = new Updater\MigrationUpdater($this->container);
        $updater->setLogger($this->logger);
        $updater->preInstall();
    }

    public function postInstall()
    {
        $updater = new Updater\MigrationUpdater($this->container->get('database_connection'), $this->container->get('doctrine.orm.entity_manager'));
        $updater->setLogger($this->logger);
        $updater->postInstall();

        $updater040100 = new Updater\Updater040100($this->container->get('doctrine.orm.entity_manager'), $this->container->get('database_connection'));
        $updater040100->setLogger($this->logger);
        $updater040100->postUpdate();
    }

    public function preUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '6.2.0', '<=')) {
            $updater040100 = new Updater\Updater060200();
            $updater040100->setLogger($this->logger);
            $updater040100->preUpdate($this->container->get('database_connection'));
        }
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '5.0.3', '<')) {
            $updater040100 = new Updater\Updater040100($this->container->get('doctrine.orm.entity_manager'), $this->container->get('database_connection'));
            $updater040100->setLogger($this->logger);
            $updater040100->postUpdate();
        }
    }
}