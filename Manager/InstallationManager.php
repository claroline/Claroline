<?php

namespace Claroline\InstallationBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Claroline\MigrationBundle\Manager\Manager;
use Claroline\MigrationBundle\Migrator\Migrator;
use Claroline\InstallationBundle\Fixtures\FixtureLoader;
use Claroline\InstallationBundle\Bundle\BundleVersion;
use Claroline\InstallationBundle\Bundle\InstallableInterface;
use Claroline\InstallationBundle\Additional\AdditionalInstallerInterface;

class InstallationManager
{
    private $container;
    private $environment;
    private $migrationManager;
    private $fixtureLoader;
    private $logger;

    public function __construct(
        ContainerInterface $container,
        Manager $migrationManager,
        FixtureLoader $fixtureLoader
    )
    {
        $this->container = $container;
        $this->environment = $kernel->getEnvironment();
        $this->migrationManager = $migrationManager;
        $this->fixtureLoader = $fixtureLoader;
    }

    public function setLogger(\Closure $logger)
    {
        $this->logger = $logger;
    }

    public function install(InstallableInterface $bundle, $requiredOnly = true)
    {
        $additionalInstaller = $this->getAdditionalInstaller($bundle);

        if ($additionalInstaller) {
            $this->log('Launching pre-installation actions...');
            $additionalInstaller->preInstall();
        }

        if ($bundle->hasMigrations()) {
            $this->log('Executing migrations...');
            $this->migrationManager->upgradeBundle($bundle, Migrator::VERSION_FARTHEST);
        }

        if ($fixturesDir = $bundle->getRequiredFixturesDirectory($this->environment)) {
            $this->log('Loading required fixtures...');
            $this->fixtureLoader->load($bundle, $fixturesDir);
        }

        if (!$requiredOnly && $fixturesDir = $bundle->getOptionalFixturesDirectory($this->environment)) {
            $this->log('Loading optional fixtures...');
            $this->fixtureLoader->load($bundle, $fixturesDir);
        }

        if ($additionalInstaller) {
            $this->log('Launching post-installation actions...');
            $additionalInstaller->postInstall();
        }
    }

    public function update(InstallableInterface $bundle, $currentVersion, $targetVersion)
    {
        $additionalInstaller = $this->getAdditionalInstaller($bundle);

        if ($additionalInstaller) {
            $this->log('Launching pre-update actions...');
            $additionalInstaller->preUpdate($current, $target);
        }

        if ($bundle->hasMigrations()) {
            $this->migrationManager->upgradeBundle($bundle, Migrator::VERSION_FARTHEST);
        }

        if ($additionalInstaller) {
            $this->log('Launching post-update actions...');
            $additionalInstaller->postUpdate($current, $target);
        }
    }

    public function uninstall(InstallableInterface $bundle)
    {
        $additionalInstaller = $this->getAdditionalInstaller($bundle);

        if ($additionalInstaller) {
            $this->log('Launching pre-uninstallation actions...');
            $additionalInstaller->preUninstall();
        }

        if ($bundle->hasMigrations()) {
            $this->log('Executing migrations...');
            $this->migrationManager->downgradeBundle($bundle, Migrator::VERSION_FARTHEST);
        }

        if ($additionalInstaller) {
            $this->log('Launching post-uninstallation actions...');
            $additionalInstaller->postUninstall();
        }

        $this->recorder->removeBundles(array(get_class($bundle)));
    }

    private function getAdditionalInstaller(InstallableInterface $bundle)
    {
        $installer = $bundle->getAdditionalInstaller();

        if ($installer instanceof AdditionalInstallerInterface) {
            $installer->setEnvironment($this->environment);
            $installer->setLogger($this->logger ?: function () {});

            if ($installer instanceof ContainerAwareInterface) {
                $installer->setContainer($this->container);
            }

            return $installer;
        }

        return false;
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log($message);
        }
    }
}
