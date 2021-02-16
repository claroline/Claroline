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

use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\CoreBundle\Library\Installation\Plugin\Recorder;
use Claroline\InstallationBundle\Additional\AdditionalInstallerInterface;
use Claroline\InstallationBundle\Bundle\InstallableInterface;
use Claroline\InstallationBundle\Fixtures\FixtureLoader;
use Claroline\KernelBundle\Bundle\PluginBundleInterface;
use Claroline\MigrationBundle\Manager\Manager;
use Claroline\MigrationBundle\Migrator\Migrator;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InstallationManager implements LoggerAwareInterface
{
    use LoggableTrait;

    /** @var ContainerInterface */
    private $container;
    /** @var string */
    private $environment;
    /** @var Manager */
    private $migrationManager;
    /** @var FixtureLoader */
    private $fixtureLoader;
    /** @var Recorder */
    private $recorder;

    /**
     * @var bool whether additional installers should re-execute updaters that have been previously executed
     */
    private $shouldReplayUpdaters = false;

    public function __construct(
        ContainerInterface $container,
        Manager $migrationManager,
        FixtureLoader $fixtureLoader,
        Recorder $recorder,
        string $environment
    ) {
        $this->container = $container;
        $this->migrationManager = $migrationManager;
        $this->fixtureLoader = $fixtureLoader;
        $this->recorder = $recorder;
        $this->environment = $environment;
    }

    public function install(InstallableInterface $bundle)
    {
        $this->fixtureLoader->setLogger($this->logger);
        $this->log(sprintf('<comment>Installing %s %s... </comment>', $bundle->getName(), $bundle->getVersion()));
        $additionalInstaller = $this->getAdditionalInstaller($bundle);

        if ($additionalInstaller) {
            $this->log('Launching pre-installation actions...');
            $additionalInstaller->preInstall();
        }

        if ($bundle->hasMigrations()) {
            $this->log('Executing migrations...');
            $this->migrationManager->upgradeBundle($bundle, Migrator::VERSION_FARTHEST);
        }

        $fixturesDir = $bundle->getRequiredFixturesDirectory($this->environment);
        if ($fixturesDir) {
            $this->log("Loading required fixtures ($fixturesDir)...");
            $this->fixtureLoader->load($bundle, $fixturesDir);
        }

        // Load configuration
        if ($bundle instanceof PluginBundleInterface) {
            $this->log('Saving configuration...');
            $this->recorder->register($bundle);
        }

        if ($additionalInstaller) {
            $this->log('Launching post-installation actions...');
            $additionalInstaller->postInstall();
        }

        $fixturesDir = $bundle->getPostInstallFixturesDirectory($this->environment);
        if ($fixturesDir) {
            $this->log("Loading post installation fixtures ($fixturesDir)...");
            $this->fixtureLoader->load($bundle, $fixturesDir);
        }
    }

    public function update(InstallableInterface $bundle, $currentVersion, $targetVersion)
    {
        $this->log(sprintf(
            '<comment>Updating %s from %s to %s...</comment>',
            $bundle->getName(),
            $currentVersion,
            $targetVersion
        ));

        $additionalInstaller = $this->getAdditionalInstaller($bundle);

        if ($additionalInstaller) {
            $this->log('Launching pre-update actions...');
            $additionalInstaller->setShouldReplayUpdaters($this->shouldReplayUpdaters);
            $additionalInstaller->preUpdate($currentVersion, $targetVersion);
        }

        if ($bundle->hasMigrations()) {
            $this->log('Executing migrations...');
            $this->migrationManager->upgradeBundle($bundle, Migrator::VERSION_FARTHEST);
        }

        $fixturesDir = $bundle->getRequiredFixturesDirectory($this->environment);
        if ($fixturesDir) {
            $this->log("Loading required fixtures ($fixturesDir)...");
            $this->fixtureLoader->load($bundle, $fixturesDir);
        }

        // Update configuration
        if ($bundle instanceof PluginBundleInterface) {
            $this->log('Updating configuration...');
            $this->recorder->update($bundle);
        }

        if ($additionalInstaller) {
            $this->log('Launching post-update actions...');
            $additionalInstaller->postUpdate($currentVersion, $targetVersion);
        }
    }

    //This function is fired at the end of a plugin installation/update.
    //This is the stuff we do no matter what, at the very end.
    //It allows us to override some stuff and it's just easier that way.
    public function end(InstallableInterface $bundle, $currentVersion = null, $targetVersion = null)
    {
        $additionalInstaller = $this->getAdditionalInstaller($bundle);

        if ($additionalInstaller) {
            $additionalInstaller->end($currentVersion, $targetVersion);
        }
    }

    public function uninstall(InstallableInterface $bundle)
    {
        $this->log(sprintf('<comment>Uninstalling %s...</comment>', $bundle->getName()));
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

    public function setShouldReplayUpdaters(bool $shouldReplayUpdaters): void
    {
        $this->shouldReplayUpdaters = $shouldReplayUpdaters;
    }

    private function getAdditionalInstaller(InstallableInterface $bundle): ?AdditionalInstallerInterface
    {
        $installer = $bundle->getAdditionalInstaller();

        if ($installer instanceof AdditionalInstallerInterface) {
            $installer->setEnvironment($this->environment);
            $installer->setLogger($this->logger);

            if ($installer instanceof ContainerAwareInterface) {
                $installer->setContainer($this->container);
            }

            return $installer;
        }

        return null;
    }
}
