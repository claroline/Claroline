<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        FixtureLoader $fixtureLoader,
        $environment
    )
    {
        $this->container = $container;
        $this->migrationManager = $migrationManager;
        $this->fixtureLoader = $fixtureLoader;
        $this->environment = $environment;
    }

    public function setLogger(\Closure $logger)
    {
        $this->logger = $logger;
    }

    public function install(InstallableInterface $bundle, $requiredOnly = true)
    {
        $this->log("<comment>Installing {$bundle->getName()}...</comment>");
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
        $this->log("<comment>Updating {$bundle->getName()}...</comment>");
        $additionalInstaller = $this->getAdditionalInstaller($bundle);

        if ($additionalInstaller) {
            $this->log('Launching pre-update actions...');
            $additionalInstaller->preUpdate($currentVersion, $targetVersion);
        }

        if ($bundle->hasMigrations()) {
            $this->log('Executing migrations...');
            $this->migrationManager->upgradeBundle($bundle, Migrator::VERSION_FARTHEST);
        }

        if ($additionalInstaller) {
            $this->log('Launching post-update actions...');
            $additionalInstaller->postUpdate($currentVersion, $targetVersion);
        }
    }

    public function uninstall(InstallableInterface $bundle)
    {
        $this->log("<comment>Uninstalling {$bundle->getName()}...</comment>");
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
