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

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Migration;
use Doctrine\DBAL\Migrations\Configuration\Configuration;

/**
 * Class responsible for executing bundle migrations.
 */
class Migrator
{
    const STATUS_CURRENT = 'current';
    const STATUS_AVAILABLE = 'available';
    const VERSION_FARTHEST = 'farthest';
    const VERSION_NEAREST = 'nearest';
    const DIRECTION_UP = 'up';
    const DIRECTION_DOWN = 'down';

    private $connection;
    private $cacheConfigs = array();

    /**
     * Constructor.
     *
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns the current version of a bundle.
     *
     * @param \Symfony\Component\HttpKernel\Bundle\Bundle $bundle
     * @return string
     */
    public function getCurrentVersion(Bundle $bundle)
    {
        return $this->getConfiguration($bundle)->getCurrentVersion();
    }

    /**
     * Returns an array containing:
     * - the current version of a bundle
     * - the list of available versions for that bundle
     *
     * Array values are index with Migrator::STATUS_* constants.
     *
     * @param \Symfony\Component\HttpKernel\Bundle\Bundle $bundle
     * @return array
     */
    public function getMigrationStatus(Bundle $bundle)
    {
        $config = $this->getConfiguration($bundle);
        $availableVersions = $config->getAvailableVersions();
        array_unshift($availableVersions, '0');

        return array(
            self::STATUS_CURRENT => $config->getCurrentVersion(),
            self::STATUS_AVAILABLE => $availableVersions
        );
    }

    /**
     * Executes a bundle migration to a specified version. Version can be
     * either an explicit version string or Migrator::VERSION_* class constant.
     * Direction must be a Migrator::DIRECTION_* class constant.
     *
     * @param \Symfony\Component\HttpKernel\Bundle\Bundle   $bundle
     * @param string                                        $version
     * @param string                                        $direction
     *
     * @return array The sql queries executed during the migration
     *
     * @throws InvalidVersionException      if the specified version is not valid
     * @throws InvalidDirectionException    if the target version is not in the specified direction
     */
    public function migrate(Bundle $bundle, $version, $direction)
    {
        $config = $this->getConfiguration($bundle);
        $currentVersion = $config->getCurrentVersion();
        $migration = new Migration($config);

        if ($version === self::VERSION_FARTHEST) {
            return $migration->migrate($direction === self::DIRECTION_UP ? null : '0');
        } elseif ($version === self::VERSION_NEAREST) {
            $availableVersions = $config->getAvailableVersions($bundle);
            array_unshift($availableVersions, '0');
            $nearestVersion = false;

            if ($direction === self::DIRECTION_DOWN) {
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

            return $nearestVersion === false ? array() : $migration->migrate($nearestVersion);
        } elseif(!is_numeric($version)) {
          throw new InvalidVersionException($version);
        } elseif ($version > $currentVersion && $direction === self::DIRECTION_DOWN
            || $version < $currentVersion && $direction === self::DIRECTION_UP) {
            throw new InvalidDirectionException($direction);
        } else {
            return $migration->migrate($version);
        }
    }

    private function getConfiguration(Bundle $bundle)
    {
        if (isset($this->cacheConfigs[$bundle->getName()])) {
            return $this->cacheConfigs[$bundle->getName()];
        }

        $driverName = $this->connection->getDriver()->getName();
        $migrationsDir = "{$bundle->getPath()}/Migrations/{$driverName}";
        $migrationsName = "{$bundle->getName()} migration";
        $migrationsNamespace = "{$bundle->getNamespace()}\\Migrations\\{$driverName}";
        $migrationsTableName = 'doctrine_' . strtolower($bundle->getName()) . '_versions';

        $config = new Configuration($this->connection);
        $config->setName($migrationsName);
        $config->setMigrationsDirectory($migrationsDir);
        $config->setMigrationsNamespace($migrationsNamespace);
        $config->setMigrationsTableName($migrationsTableName);
        $config->registerMigrationsFromDirectory($migrationsDir);

        $this->cacheConfigs[$bundle->getName()] = $config;

        return $config;
    }
}
