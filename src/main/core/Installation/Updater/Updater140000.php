<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\InstallationBundle\Bundle\InstallableInterface;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpKernel\KernelInterface;

class Updater140000 extends Updater
{
    private KernelInterface $kernel;
    private Connection $connection;

    public function __construct(
        KernelInterface $kernel,
        Connection $connection
    ) {
        $this->kernel = $kernel;
        $this->connection = $connection;
    }

    public function preUpdate()
    {
        // Adds migration FQCN in the versions tables (required by new doctrine migrations version)
        $this->renameMigrations();

        // the namespace of the migrations has changed (eg. removed the `pdo_mysql` part)
        // Doctrine will try to re-execute migrations because of the renaming
        // we need to update the version classnames in the DB to avoid breaking updates
        $this->log('Updating doctrine migration versions...');
        // retrieve all doctrine versions tables
        $stmt = $this->connection->prepare('
            SHOW TABLES LIKE "doctrine_%_versions"
        ');

        $results = $stmt->executeQuery();
        $tables = $results->fetchFirstColumn();

        foreach ($tables as $table) {
            $this->log(sprintf('Updating doctrine migration versions %s...', $table));

            // update last version execution
            $versionsQuery = $this->connection->prepare("
                SELECT * FROM {$table} ORDER BY version ASC
            ");

            $results = $versionsQuery->executeQuery();
            $versions = $results->fetchAllAssociative();

            foreach ($versions as $version) {
                $className = $version['version'];
                if (!class_exists($version['version']) && false !== strpos($version['version'], 'pdo_mysql')) {
                    $className = str_replace('\\pdo_mysql', '', $version['version']);
                }

                if (!class_exists($className)) {
                    // migration version has been removed, we can remove it from the table
                    $deleteQuery = $this->connection->prepare("
                        DELETE FROM {$table} 
                        WHERE `version` = :version
                    ");

                    $deleteQuery->executeQuery(['version' => $version['version']]);
                } elseif ($version['version'] !== $className) {
                    // migration version has been renamed
                    $updateVersionQuery = $this->connection->prepare("
                        UPDATE {$table} SET `version` = :updatedVersion WHERE version = :version
                    ");

                    $updateVersionQuery->executeQuery([
                        'version' => $version['version'],
                        'updatedVersion' => $className,
                    ]);
                }
            }
        }
    }

    private function renameMigrations(): void
    {
        $toMigrate = [];

        $bundles = $this->kernel->getBundles();
        foreach ($bundles as $bundle) {
            if ($bundle instanceof InstallableInterface) {
                $installer = $bundle->getAdditionalInstaller();
                if ($installer && $installer->hasMigrations()) {
                    $toMigrate[] = $bundle;
                }
            }
        }

        foreach ($toMigrate as $bundleToMigrate) {
            $migrationsTableName = 'doctrine_'.strtolower($bundleToMigrate->getName()).'_versions';

            $createQuery = $this->connection->prepare("
                CREATE TABLE IF NOT EXISTS {$migrationsTableName} (
                  `version` VARCHAR(191) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
                  `executed_at` DATETIME DEFAULT NULL,
                  `execution_time` INT DEFAULT NULL,
                  PRIMARY KEY (`version`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci
            ");
            $createQuery->executeQuery();

            $alterQuery = $this->connection->prepare("
                ALTER TABLE {$migrationsTableName} CHANGE `version` `version` VARCHAR(191) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL
            ");

            $alterQuery->executeQuery();

            $updateVersionQuery = $this->connection->prepare("
                UPDATE {$migrationsTableName} SET `version` = CONCAT(:migrationNamespace, `version`) WHERE `version` NOT LIKE '%Migrations%'
            ");

            $updateVersionQuery->executeQuery([
                'migrationNamespace' => $bundleToMigrate->getNamespace().'\\Installation\\Migrations\\pdo_mysql\\Version',
            ]);
        }
    }
}
