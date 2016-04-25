<?php

namespace Icap\BadgeBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function preInstall()
    {
        $updater = new Updater\MigrationUpdater($this->container->get('database_connection'),
            $this->container->get('doctrine.orm.entity_manager'), $this->container->get('icap_badge.factory.portfolio_widget'));
        $updater->setLogger($this->logger);
        $updater->preInstall();
    }

    public function postInstall()
    {
        $updater = new Updater\MigrationUpdater($this->container->get('database_connection'),
            $this->container->get('doctrine.orm.entity_manager'), $this->container->get('icap_badge.factory.portfolio_widget'));
        $updater->setLogger($this->logger);
        $updater->postInstall();

        $updater060300 = new Updater\Updater060300($this->container);
        $updater060300->setLogger($this->logger);
        $updater060300->postInstall($this->container->get('doctrine.dbal.default_connection'), $this->container->get('kernel'));
    }

    public function preUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '6.2.0', '<=')) {
            $updater040100 = new Updater\Updater060200();
            $updater040100->setLogger($this->logger);
            $updater040100->preUpdate($this->container->get('database_connection'), $this->container->get('kernel'));
        }
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '5.0.3', '<')) {
            $updater040100 = new Updater\Updater040100($this->container->get('doctrine.orm.entity_manager'), $this->container->get('database_connection'));
            $updater040100->setLogger($this->logger);
            $updater040100->postUpdate();
        }

        if (version_compare($currentVersion, '6.3.0', '<=')) {
            $updater060300 = new Updater\Updater060300($this->container);
            $updater060300->setLogger($this->logger);
            $updater060300->postUpdate($this->container->get('doctrine.dbal.default_connection'), $this->container->get('kernel'));
        }
    }

    public function preUninstall()
    {
        $updater = new Updater\MigrationUpdater($this->container->get('database_connection'),
            $this->container->get('doctrine.orm.entity_manager'), $this->container->get('icap_badge.factory.portfolio_widget'));
        $updater->setLogger($this->logger);
        $updater->preUninstall();
    }

    public function postUninstall()
    {
        $updater = new Updater\MigrationUpdater($this->container->get('database_connection'),
            $this->container->get('doctrine.orm.entity_manager'), $this->container->get('icap_badge.factory.portfolio_widget'));
        $updater->setLogger($this->logger);
        $updater->postUninstall();
    }
}
