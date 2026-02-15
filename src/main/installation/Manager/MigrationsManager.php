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

use Claroline\InstallationBundle\Migrations\Generator;
use Claroline\InstallationBundle\Migrations\Migrator;
use Claroline\InstallationBundle\Migrations\Writer;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class MigrationsManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly Connection $connection,
        private readonly Generator $generator,
        private readonly Writer $writer,
        private readonly Migrator $migrator
    ) {
    }

    /**
     * Generates bundle migrations classes for all the available driver platforms.
     */
    public function generateBundleMigration(BundleInterface $bundle): void
    {
        $config = $this->migrator->getConfiguration($bundle);

        $versionName = $config->generateClassName();

        $this->log("Generating migrations classes for '{$bundle->getName()}'...");

        $queries = $this->generator->generateMigrationQueries($bundle, $this->connection->getDatabasePlatform());

        if (count($queries[Generator::QUERIES_UP]) > 0 || count($queries[Generator::QUERIES_DOWN]) > 0) {
            $this->log('Generating migration class...');
            $this->writer->writeMigrationClass($bundle, $versionName, $queries);
        } else {
            $this->log('Nothing to generate: database and mapping are synced');
        }
    }

    /**
     * Returns information about the migration status of a bundle. The return
     * value is the same as Migrator::getMigrationStatus().
     */
    public function getBundleStatus(BundleInterface $bundle): array
    {
        return $this->migrator->getMigrationStatus($bundle);
    }

    /**
     * Upgrades a bundle to a specified version. The version can be either an
     * explicit version string or a Migrator::VERSION_* constant.
     */
    public function upgradeBundle(BundleInterface $bundle, string $version): void
    {
        $this->doMigrate($bundle, $version, Migrator::DIRECTION_UP);
    }

    /**
     * Upgrades a bundle to a specified version. The version can be either an
     * explicit version string or a Migrator::VERSION_* constant.
     */
    public function downgradeBundle(BundleInterface $bundle, string $version): void
    {
        $this->doMigrate($bundle, $version, Migrator::DIRECTION_DOWN);
    }

    private function doMigrate(BundleInterface $bundle, $version, $direction): void
    {
        $action = Migrator::DIRECTION_UP === $direction ? 'Ugprading' : 'Downgrading';
        $this->log("{$action} bundle '{$bundle->getName()}'...");
        $queries = $this->migrator->migrate($bundle, $version, $direction);
        $currentVersion = $this->migrator->getCurrentVersion($bundle);

        $this->log(
            !$queries || 0 === count($queries) ?
                "Nothing to execute: bundle is already at version {$currentVersion}" :
                "Done: bundle is now at version {$currentVersion}"
        );
    }

    private function log(string $message): void
    {
        if ($this->logger) {
            $this->logger->log(LogLevel::INFO, $message);
        }
    }
}
