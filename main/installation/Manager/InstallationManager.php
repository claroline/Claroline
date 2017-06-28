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

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\InstallationBundle\Additional\AdditionalInstallerInterface;
use Claroline\InstallationBundle\Bundle\InstallableInterface;
use Claroline\InstallationBundle\Fixtures\FixtureLoader;
use Claroline\MigrationBundle\Manager\Manager;
use Claroline\MigrationBundle\Migrator\Migrator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InstallationManager
{
    use LoggableTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var Manager
     */
    private $migrationManager;

    /**
     * @var FixtureLoader
     */
    private $fixtureLoader;

    public function __construct(
        ContainerInterface $container,
        Manager $migrationManager,
        FixtureLoader $fixtureLoader,
        $environment
    ) {
        $this->container = $container;
        $this->migrationManager = $migrationManager;
        $this->fixtureLoader = $fixtureLoader;
        $this->environment = $environment;
    }

    public function install(InstallableInterface $bundle, $insertPlugin = true)
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

        if ($fixturesDir = $bundle->getRequiredFixturesDirectory($this->environment)) {
            $this->log("Loading required fixtures ($fixturesDir)...");
            $this->fixtureLoader->load($bundle, $fixturesDir);
        }

        if ($additionalInstaller) {
            $this->log('Launching post-installation actions...');
            $additionalInstaller->postInstall();
        }

        if ($insertPlugin) {
            $this->container->get('claroline.manager.version_manager')->setLogger($this->logger);
            $version = $this->container->get('claroline.manager.version_manager')->register($bundle);
            $validator = $this->container->get('claroline.plugin.validator');
            $installer = $this->container->get('claroline.plugin.installer');
            $dbWriter = $this->container->get('claroline.plugin.recorder_database_writer');
            $dbWriter->setLogger($this->logger);
            $this->log('Parsing config.yml file for '.get_class($bundle).'...');
            $installer->validatePlugin($bundle);
            $dbWriter->insert($bundle, $validator->getPluginConfiguration());
            $version = $this->container->get('claroline.manager.version_manager')->execute($version);
        }

        if ($fixturesDir = $bundle->getPostInstallFixturesDirectory($this->environment)) {
            $this->log("Loading post installation fixtures ($fixturesDir)...");
            $this->fixtureLoader->load($bundle, $fixturesDir);
        }
    }

    public function update(InstallableInterface $bundle, $currentVersion, $targetVersion)
    {
        if (strpos($currentVersion, 'dev') === 0) {
            $currentVersion = '9999999-'.$currentVersion;
        }
        if (strpos($targetVersion, 'dev') === 0) {
            $targetVersion = '9999999-'.$targetVersion;
        }

        $this->log(sprintf(
            '<comment>Updating %s from %s to %s...</comment>',
            $bundle->getName(),
            $currentVersion,
            $targetVersion
        ));
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

    //This function is fired at the end of a plugin installation/update.
    //This is the stuff we do no matter what, at the very end.
    //It allows us to override some stuff and it's just easier that way.
    public function end(InstallableInterface $bundle)
    {
        $additionalInstaller = $this->getAdditionalInstaller($bundle);

        if ($additionalInstaller) {
            $additionalInstaller->end();
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

    /**
     * @param InstallableInterface $bundle
     *
     * @return AdditionalInstallerInterface|bool
     */
    private function getAdditionalInstaller(InstallableInterface $bundle)
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

        return false;
    }
}
