<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MigrationBundle\Migrator;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Class responsible for executing bundle migrations.
 */
class Migrator
{
    const STATUS_CURRENT = 'current';
    const STATUS_AVAILABLE = 'available';
    const VERSION_FARTHEST = 'farthest';
    const VERSION_NEAREST = 'nearest';
    const VERSION_LATEST = 'latest';
    const DIRECTION_UP = 'up';
    const DIRECTION_DOWN = 'down';

    private $connection;
    private $cacheConfigs = [];

    /**
     * Constructor.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns the current version of a bundle.
     *
     * @return string
     */
    public function getCurrentVersion(BundleInterface $bundle)
    {
        return $this->getConfiguration($bundle)->getCurrentVersion();
    }

    /**
     * Returns an array containing:
     * - the current version of a bundle
     * - the list of available versions for that bundle.
     *
     * Array values are index with Migrator::STATUS_* constants.
     *
     * @return array
     */
    public function getMigrationStatus(BundleInterface $bundle)
    {
        $config = $this->getConfiguration($bundle);

        if (is_dir($config->getMigrationsDirectory())) {
            $currentVersion = $config->getCurrentVersion();
            $availableVersions = $config->getAvailableVersions();
            array_unshift($availableVersions, '0');
        } else {
            $currentVersion = '0';
            $availableVersions = [];
        }

        return [
            self::STATUS_CURRENT => $currentVersion,
            self::STATUS_AVAILABLE => $availableVersions,
            self::VERSION_LATEST => $config->getLatestVersion(),
        ];
    }

    /**
     * Executes a bundle migration to a specified version. Version can be
     * either an explicit version string or Migrator::VERSION_* class constant.
     * Direction must be a Migrator::DIRECTION_* class constant.
     *
     * @param string $version
     * @param string $direction
     *
     * @return array The sql queries executed during the migration
     *
     * @throws InvalidVersionException   if the specified version is not valid
     * @throws InvalidDirectionException if the target version is not in the specified direction
     */
    public function migrate(BundleInterface $bundle, $version, $direction)
    {
        $config = $this->getConfiguration($bundle);
        $currentVersion = $config->getCurrentVersion();
        $migration = $config->getDependencyFactory()->getMigrator();

        if (self::VERSION_FARTHEST === $version) {
            return $migration->migrate(self::DIRECTION_UP === $direction ? null : '0');
        } elseif (self::VERSION_NEAREST === $version) {
            $availableVersions = $config->getAvailableVersions($bundle);
            array_unshift($availableVersions, '0');
            $nearestVersion = false;

            if (self::DIRECTION_DOWN === $direction) {
                $availableVersions = array_reverse($availableVersions);
            }

            foreach ($availableVersions as $index => $availableVersion) {
                if ($currentVersion === $availableVersion) {
                    $nearestVersionIndex = ++$index;

                    if (isset($availableVersions[$nearestVersionIndex])) {
                        $nearestVersion = $availableVersions[$nearestVersionIndex];
                    }

                    break;
                }
            }

            return false === $nearestVersion ? [] : $migration->migrate($nearestVersion);
        } elseif (!is_numeric($version)) {
            throw new InvalidVersionException($version);
        } elseif ($version > $currentVersion && self::DIRECTION_DOWN === $direction
            || $version < $currentVersion && self::DIRECTION_UP === $direction) {
            throw new InvalidDirectionException($direction);
        } else {
            return $migration->migrate($version);
        }
    }

    private function getConfiguration(BundleInterface $bundle)
    {
        if (isset($this->cacheConfigs[$bundle->getName()])) {
            return $this->cacheConfigs[$bundle->getName()];
        }

        $driverName = $this->connection->getDriver()->getName();

        $migrationsDir = implode(DIRECTORY_SEPARATOR, [$bundle->getPath(), 'Installation', 'Migrations', $driverName]);
        $migrationsName = "{$bundle->getName()} migration";
        $migrationsNamespace = "{$bundle->getNamespace()}\\Installation\\Migrations\\{$driverName}";
        $migrationsTableName = 'doctrine_'.strtolower($bundle->getName()).'_versions';

        $config = new Configuration($this->connection);
        $config->setName($migrationsName);
        $config->setMigrationsDirectory($migrationsDir);
        $config->setMigrationsNamespace($migrationsNamespace);
        $config->setMigrationsTableName($migrationsTableName);

        if (is_dir($migrationsDir)) {
            $config->registerMigrationsFromDirectory($migrationsDir);
        }

        $this->cacheConfigs[$bundle->getName()] = $config;

        return $config;
    }

    public function markMigrated(BundleInterface $bundle, $version)
    {
        $config = $this->getConfiguration($bundle);
        $config->getVersion($version)->markMigrated();
    }

    public function markNotMigrated(BundleInterface $bundle, $version)
    {
        $config = $this->getConfiguration($bundle);
        $config->getVersion($version)->markNotMigrated();
    }

    public function markAllMigrated(BundleInterface $bundle)
    {
        $config = $this->getConfiguration($bundle);

        foreach ($config->getAvailableVersions() as $version) {
            $version = $config->getVersion($version);
            if (!$config->hasVersionMigrated($version)) {
                $version->markMigrated();
            }
        }
    }
}
