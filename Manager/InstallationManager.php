<?php

namespace Claroline\InstallationBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Claroline\MigrationBundle\Manager\Manager;
use Claroline\MigrationBundle\Migrator\Migrator;
use Claroline\InstallationBundle\Fixtures\FixtureLoader;
use Claroline\InstallationBundle\Bundle\InstallableInterface;

class InstallationManager
{
    private $container;
    private $environment;
    private $migrationManager;
    private $fixtureLoader;

    public function __construct(ContainerInterface $container, Manager $migrationManager, FixtureLoader $fixtureLoader)
    {
        $this->container = $container;
        $this->environment = $container->get('kernel')->getEnvironment();
        $this->migrationManager = $migrationManager;
        $this->fixtureLoader = $fixtureLoader;
    }

    public function install(InstallableInterface $bundle, $requiredOnly = true)
    {
        if ($action = $bundle->getPreInstallationAction($this->environment)) {
            $parts = explode('#', $action);
            $object = new $parts[0];

            if ($object instanceof ContainerAwareInterface) {
                $object->setContainer($this->container);
            }

            $object->{$parts[1]}();
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
}
