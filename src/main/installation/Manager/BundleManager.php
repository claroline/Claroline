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

use Claroline\CoreBundle\Library\Installation\Plugin\Recorder;
use Claroline\InstallationBundle\Additional\AdditionalInstallerInterface;
use Claroline\InstallationBundle\Bundle\InstallableInterface;
use Claroline\InstallationBundle\Fixtures\FixtureLoader;
use Claroline\InstallationBundle\Fixtures\PostInstallInterface;
use Claroline\InstallationBundle\Fixtures\PostUpdateInterface;
use Claroline\InstallationBundle\Fixtures\PreInstallInterface;
use Claroline\InstallationBundle\Fixtures\PreUpdateInterface;
use Claroline\KernelBundle\Bundle\PluginBundleInterface;
use Claroline\MigrationBundle\Manager\Manager;
use Claroline\MigrationBundle\Migrator\Migrator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manages installation for Installable bundles.
 */
class BundleManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Whether additional installers should re-execute updaters that have been previously executed.
     */
    private bool $shouldReplayUpdaters = false;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly Manager $migrationManager,
        private readonly FixtureLoader $fixtureLoader,
        private readonly Recorder $recorder
    ) {
    }

    public function setShouldReplayUpdaters(bool $shouldReplayUpdaters): void
    {
        $this->shouldReplayUpdaters = $shouldReplayUpdaters;
    }

    public function install(InstallableInterface $bundle): void
    {
        $this->logger->info(sprintf('Installing %s %s...', $bundle->getName(), $bundle->getVersion()));

        $additionalInstaller = $this->getAdditionalInstaller($bundle);
        if (!$additionalInstaller) {
            // Load configuration
            if ($bundle instanceof PluginBundleInterface) {
                $this->logger->info('Saving configuration...');
                $this->recorder->register($bundle);
            }

            // no additional installer for the plugin so there is nothing to do
            return;
        }

        $this->logger->info('Launching pre-installation actions...');
        $additionalInstaller->preInstall();

        if ($additionalInstaller->hasMigrations()) {
            $this->logger->info('Executing migrations...');
            $this->migrationManager->upgradeBundle($bundle, Migrator::VERSION_LATEST);
        }

        if ($additionalInstaller->hasFixtures()) {
            $this->logger->info('Loading pre-install fixtures...');
            $this->fixtureLoader->load($bundle, PreInstallInterface::class);
        }

        // Load configuration
        if ($bundle instanceof PluginBundleInterface) {
            $this->logger->info('Saving configuration...');
            $this->recorder->register($bundle);
        }

        $this->logger->info('Launching post-installation actions...');
        $additionalInstaller->postInstall();

        if ($additionalInstaller->hasFixtures()) {
            $this->logger->info('Loading post-install fixtures...');
            $this->fixtureLoader->load($bundle, PostInstallInterface::class);
        }
    }

    public function update(InstallableInterface $bundle, $currentVersion, $targetVersion): void
    {
        $this->logger->info(sprintf('Updating %s from %s to %s...', $bundle->getName(), $currentVersion, $targetVersion));

        $additionalInstaller = $this->getAdditionalInstaller($bundle);
        if (!$additionalInstaller) {
            // Update configuration
            if ($bundle instanceof PluginBundleInterface) {
                $this->recorder->update($bundle);
            }

            // no additional installer for the plugin so there is nothing to do
            return;
        }

        $this->logger->debug('Launching pre-update actions...');
        $additionalInstaller->setShouldReplayUpdaters($this->shouldReplayUpdaters);
        $additionalInstaller->preUpdate($currentVersion, $targetVersion);

        if ($additionalInstaller->hasMigrations()) {
            $this->logger->debug('Executing migrations...');
            $this->migrationManager->upgradeBundle($bundle, Migrator::VERSION_LATEST);
        }

        if ($additionalInstaller->hasFixtures()) {
            $this->logger->debug('Loading pre-update fixtures...');
            $this->fixtureLoader->load($bundle, PreUpdateInterface::class);
        }

        // Update configuration
        if ($bundle instanceof PluginBundleInterface) {
            $this->recorder->update($bundle);
        }

        $this->logger->debug('Launching post-update actions...');
        $additionalInstaller->postUpdate($currentVersion, $targetVersion);

        if ($additionalInstaller->hasFixtures()) {
            $this->logger->debug('Loading post-update fixtures...');
            $this->fixtureLoader->load($bundle, PostUpdateInterface::class);
        }
    }

    // This function is fired at the end of a plugin installation/update.
    // This is the stuff we do no matter what, at the very end.
    // It allows us to override some stuff, and it's just easier that way.
    public function end(InstallableInterface $bundle, $currentVersion = null, $targetVersion = null): void
    {
        $additionalInstaller = $this->getAdditionalInstaller($bundle);

        if ($additionalInstaller) {
            $additionalInstaller->end($currentVersion, $targetVersion);
        }
    }

    public function uninstall(InstallableInterface $bundle): void
    {
        $this->logger->info(sprintf('Uninstalling %s...', $bundle->getName()));

        $additionalInstaller = $this->getAdditionalInstaller($bundle);
        if (!$additionalInstaller) {
            // no additional installer for the plugin so there is nothing to do
            return;
        }

        $this->logger->info('Launching pre-uninstallation actions...');
        $additionalInstaller->preUninstall();

        if ($additionalInstaller->hasMigrations()) {
            $this->logger->info('Executing migrations...');
            $this->migrationManager->downgradeBundle($bundle, Migrator::VERSION_LATEST);
        }

        $this->logger->info('Launching post-uninstallation actions...');
        $additionalInstaller->postUninstall();
    }

    private function getAdditionalInstaller(InstallableInterface $bundle): ?AdditionalInstallerInterface
    {
        $installer = $bundle->getAdditionalInstaller();

        if ($installer instanceof AdditionalInstallerInterface) {
            if ($installer instanceof LoggerAwareInterface) {
                $installer->setLogger($this->logger);
            }

            if ($installer instanceof ContainerAwareInterface) {
                $installer->setContainer($this->container);
            }

            return $installer;
        }

        return null;
    }
}
