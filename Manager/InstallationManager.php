<?php

namespace Claroline\InstallationBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Claroline\MigrationBundle\Manager\Manager;
use Claroline\MigrationBundle\Migrator\Migrator;
use Claroline\InstallationBundle\Fixtures\FixtureLoader;
use Claroline\InstallationBundle\Bundle\InstallableInterface;
use Claroline\InstallationBundle\Additional\AdditionalInstallerInterface;

class InstallationManager implements LoggerAwareInterface
{
    private $container;
    private $environment;
    private $migrationManager;
    private $fixtureLoader;
    private $logger;

    public function __construct(ContainerInterface $container, Manager $migrationManager, FixtureLoader $fixtureLoader)
    {
        $this->container = $container;
        $this->environment = $container->get('kernel')->getEnvironment();
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
            $additionalInstaller->preInstall();
        }

        if ($bundle->hasMigrations()) {
            $this->migrationManager->upgradeBundle($bundle, Migrator::VERSION_FARTHEST);
        }

        if ($fixturesDir = $bundle->getRequiredFixturesDirectory($this->environment)) {
            $this->fixtureLoader->load($bundle, $fixturesDir);
        }

        if (!$requiredOnly && $fixturesDir = $bundle->getOptionalFixturesDirectory($this->environment)) {
            $this->fixtureLoader->load($bundle, $fixturesDir);
        }
    }

    public function uninstall(InstallableInterface $bundle)
    {
        if ($bundle->hasMigrations()) {
            $this->migrationManager->downgradeBundle($bundle, Migrator::VERSION_FARTHEST);
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
}
