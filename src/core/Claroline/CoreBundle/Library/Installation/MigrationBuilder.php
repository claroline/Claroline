<?php

namespace Claroline\CoreBundle\Library\Installation;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Migration;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.install.migration_builder")
 */
class MigrationBuilder
{
    private $connection;
    private $migrationHelper;
    private $prodMigrationsRelativePath;
    private $testMigrationsRelativePath;
    private $includeTestMigrations;
    private $driverName;

    /**
     * @DI\InjectParams({
     *     "connection" = @DI\Inject("doctrine.dbal.default_connection"),
     *     "helper" = @DI\Inject("claroline.install.migration_helper"),
     *     "prodMigrationsRelativePath" = @DI\Inject("%claroline.param.prod_migrations_directory%"),
     *     "testMigrationsRelativePath" = @DI\Inject("%claroline.param.test_migrations_directory%"),
     *     "includeTestMigrations" = @DI\Inject("%claroline.param.include_test_migrations%")
     * })
     */
    public function __construct(
        Connection $connection,
        MigrationHelper $helper,
        $prodMigrationsRelativePath,
        $testMigrationsRelativePath,
        $includeTestMigrations = false
    )
    {
        $this->connection = $connection;
        $this->migrationHelper = $helper;
        $this->driverName = $connection->getDriver()->getName();
        $this->prodMigrationsRelativePath = $prodMigrationsRelativePath;
        $this->testMigrationsRelativePath = $testMigrationsRelativePath;
        $this->includeTestMigrations = (bool) $includeTestMigrations;
    }

    public function buildMigrationsForBundle(Bundle $bundle)
    {
        $migrations = array();
        $prodMigration = $this->buildMigration($bundle, $this->prodMigrationsRelativePath, 'prod');

        if (false !== $prodMigration) {
            $migrations[] = $prodMigration;
        }

        if (true === $this->includeTestMigrations) {
            $testMigration = $this->buildMigration($bundle, $this->testMigrationsRelativePath, 'test');

            if (false !== $testMigration) {
                $migrations[] = $testMigration;
            }
        }

        return $migrations;
    }

    private function buildMigration(Bundle $bundle, $migrationsRelativePath, $environment)
    {
        $bundlePrefix = $this->migrationHelper->getTablePrefixForBundle($bundle);
        $environment == "prod" ? $tableDiscr = '' : $tableDiscr = "_test";
        $migrationsPath = "{$bundle->getPath()}/{$migrationsRelativePath}";
        $migrationsName = "{$bundle->getName()} {$environment} migration";
        $migrationsNamespace = "{$bundle->getNamespace()}\\"
            . str_replace('/', '\\', $migrationsRelativePath);
        $migrationsTableName = "{$bundlePrefix}{$tableDiscr}_doctrine_migration_versions";
        $isDriverSpecific = is_dir("{$migrationsPath}/{$this->driverName}");

        $config = new Configuration($this->connection);
        $config->setName($migrationsName);
        $config->setMigrationsDirectory(
            $isDriverSpecific ? "{$migrationsPath}/{$this->driverName}" : $migrationsPath
        );
        $config->setMigrationsNamespace(
            $isDriverSpecific ? "{$migrationsNamespace}\\{$this->driverName}" : $migrationsNamespace
        );
        $config->setMigrationsTableName($migrationsTableName);
        $config->registerMigrationsFromDirectory(
            $isDriverSpecific ? "{$migrationsPath}/{$this->driverName}" : $migrationsPath
        );

        if (count($config->getMigrations()) == 0) {
            return false;
        }

        return new Migration($config);
    }
}
