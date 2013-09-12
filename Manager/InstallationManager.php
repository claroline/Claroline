<?php

namespace Claroline\InstallationBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Claroline\MigrationBundle\Manager\Manager;
use Claroline\MigrationBundle\Migrator\Migrator;
use Claroline\BundleRecorder\Recorder;
use Claroline\KernelBundle\Kernel\SwitchKernel;
use Claroline\InstallationBundle\Fixtures\FixtureLoader;
use Claroline\InstallationBundle\Bundle\InstallableInterface;
use Claroline\InstallationBundle\Additional\AdditionalInstallerInterface;

class InstallationManager
{
    private $container;
    private $environment;
    private $kernel;
    private $recorder;
    private $migrationManager;
    private $fixtureLoader;
    private $logger;

    public function __construct(
        ContainerInterface $container,
        SwitchKernel $kernel,
        Recorder $recorder,
        Manager $migrationManager,
        FixtureLoader $fixtureLoader
    )
    {
        $this->container = $container;
        $this->environment = $kernel->getEnvironment();
        $this->kernel = $kernel;
        $this->recorder = $recorder;
        $this->migrationManager = $migrationManager;
        $this->fixtureLoader = $fixtureLoader;
    }

    public function setLogger(\Closure $logger)
    {
        $this->logger = $logger;
        $this->recorder->setLogger($logger);
    }

    public function install(InstallableInterface $bundle, $requiredOnly = true)
    {
        $this->recorder->addBundles(array(get_class($bundle)));
        $this->kernel->switchToTmpEnvironment();
        $this->container = $this->kernel->getContainer();
        // bundle mapping is only accessible in the new container instance
        $this->fixtureLoader = $this->container->get('claroline.installation.fixture_loader');

        try {
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
        } catch (\Exception $ex) {
            $this->log('<error>An error occured !</error>');
            $this->recorder->removeBundles(array(get_class($bundle)));
            $this->kernel->switchBack(true);

            throw $ex;
        }

        $this->kernel->switchBack(true);
    }

    public function uninstall(InstallableInterface $bundle)
    {
        if ($bundle->hasMigrations()) {
            $this->log('Executing migrations...');
            $this->migrationManager->downgradeBundle($bundle, Migrator::VERSION_FARTHEST);
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
