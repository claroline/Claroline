<?php

namespace Claroline\MigrationBundle\Migrator;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\AvailableMigration;
use Doctrine\Migrations\Metadata\Storage\MetadataStorage;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\Migrations\MigratorConfiguration;
use Doctrine\Migrations\Version\Version;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class BundleMigration
{
    private string $namespace;

    private DependencyFactory $dependencyFactory;

    public function __construct(Connection $connection, BundleInterface $bundle)
    {
        $migrationsDir = implode(DIRECTORY_SEPARATOR, [$bundle->getPath(), 'Installation', 'Migrations']);
        $this->namespace = "{$bundle->getNamespace()}\\Installation\\Migrations";
        $migrationsTableName = 'doctrine_'.strtolower($bundle->getName()).'_versions';

        $metadata = new TableMetadataStorageConfiguration();
        $metadata->setTableName($migrationsTableName);

        $configuration = new Configuration();
        $configuration->setMetadataStorageConfiguration($metadata);
        // Whether to add a database platform check at the beginning of the generated code.
        $configuration->setCheckDatabasePlatform(false);

        if (is_dir($migrationsDir)) {
            $configuration->addMigrationsDirectory($this->namespace, $migrationsDir);
        }

        $this->dependencyFactory = DependencyFactory::fromConnection(
            new ExistingConfiguration($configuration),
            new ExistingConnection($connection)
        );

        // make sure the metadata storage is initialized and up-to-date before manipulating the bundle migrations
        $this->getMetadataStorage()->ensureInitialized();
    }

    /**
     * Returns the current version of a bundle.
     */
    public function getCurrentVersion(): ?Version
    {
        return $this->getVersion('current');
    }

    public function getVersion(string $alias): ?Version
    {
        return $this->dependencyFactory->getVersionAliasResolver()->resolveVersionAlias($alias);
    }

    public function getAvailableVersions(): array
    {
        return array_map(function (AvailableMigration $available): Version {
            return $available->getVersion();
        }, $this->dependencyFactory->getMigrationRepository()->getMigrations()->getItems());
    }

    public function hasVersionMigrated(Version $version): bool
    {
        $migrationStatus = $this->dependencyFactory->getMigrationStatusCalculator();

        $executedMigrations = $migrationStatus->getExecutedUnavailableMigrations();
        foreach ($executedMigrations as $executedMigration) {
            if ($executedMigration->hasMigration($version)) {
                return true;
            }
        }

        return false;
    }

    public function generateClassName(): string
    {
        return $this->dependencyFactory->getClassNameGenerator()->generateClassName($this->namespace);
    }

    public function getMetadataStorage(): MetadataStorage
    {
        return $this->dependencyFactory->getMetadataStorage();
    }

    public function migrate(Version $targetVersion): array
    {
        $migratorConfiguration = (new MigratorConfiguration())
            ->setDryRun(false)
            ->setTimeAllQueries(true)
            ->setAllOrNothing(true);

        $planCalculator = $this->dependencyFactory->getMigrationPlanCalculator();
        $plan = $planCalculator->getPlanUntilVersion($targetVersion);

        return $this->dependencyFactory->getMigrator()->migrate($plan, $migratorConfiguration);
    }
}
