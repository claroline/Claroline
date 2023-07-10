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
use Doctrine\Migrations\Version\Direction;
use Doctrine\Migrations\Version\ExecutionResult;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Class responsible for executing bundle migrations.
 */
class Migrator
{
    const STATUS_CURRENT = 'current';
    const STATUS_AVAILABLE = 'available';

    const VERSION_NEAREST = 'nearest';
    const VERSION_LATEST = 'latest';

    const DIRECTION_UP = 'up';
    const DIRECTION_DOWN = 'down';

    private Connection $connection;
    /** @var BundleMigration[] */
    private array $cacheConfigs = [];

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * Returns the current version of a bundle.
     */
    public function getCurrentVersion(BundleInterface $bundle): string
    {
        return (string) $this->getConfiguration($bundle)->getCurrentVersion();
    }

    /**
     * Returns an array containing:
     * - the current version of a bundle
     * - the list of available versions for that bundle.
     *
     * Array values are index with Migrator::STATUS_* constants.
     */
    public function getMigrationStatus(BundleInterface $bundle): array
    {
        $config = $this->getConfiguration($bundle);

        return [
            self::STATUS_CURRENT => $config->getVersion('current'),
            self::VERSION_LATEST => $config->getVersion('latest'),
            self::STATUS_AVAILABLE => $config->getAvailableVersions(),
        ];
    }

    /**
     * Executes a bundle migration to a specified version. Version can be
     * either an explicit version string or Migrator::VERSION_* class constant.
     * Direction must be a Migrator::DIRECTION_* class constant.
     *
     * @throws InvalidVersionException   if the specified version is not valid
     * @throws InvalidDirectionException if the target version is not in the specified direction
     */
    public function migrate(BundleInterface $bundle, string $version, string $direction): array
    {
        $config = $this->getConfiguration($bundle);
        $currentVersion = $config->getCurrentVersion();

        if (self::VERSION_LATEST === $version) {
            $targetVersion = $config->getVersion(self::DIRECTION_DOWN === $direction ? 'first' : 'latest');
        } elseif (self::VERSION_NEAREST === $version) {
            $targetVersion = $config->getVersion(self::DIRECTION_DOWN === $direction ? 'prev' : 'next');
        } elseif (!is_numeric($version)) {
            throw new InvalidVersionException($version);
        } elseif ($version > $currentVersion && self::DIRECTION_DOWN === $direction
            || $version < $currentVersion && self::DIRECTION_UP === $direction) {
            throw new InvalidDirectionException($direction);
        } else {
            $targetVersion = $config->getVersion($version);
        }

        return $config->migrate($targetVersion);
    }

    public function markMigrated(BundleInterface $bundle, string $version): void
    {
        $config = $this->getConfiguration($bundle);

        $targetVersion = $config->getVersion($version);
        if (!$config->hasVersionMigrated($targetVersion)) {
            $migrationResult = new ExecutionResult($targetVersion, Direction::UP);
            $config->getMetadataStorage()->complete($migrationResult);
        }
    }

    public function markNotMigrated(BundleInterface $bundle, string $version): void
    {
        $config = $this->getConfiguration($bundle);

        $targetVersion = $config->getVersion($version);
        if ($config->hasVersionMigrated($targetVersion)) {
            $migrationResult = new ExecutionResult($targetVersion, Direction::DOWN);
            $config->getMetadataStorage()->complete($migrationResult);
        }
    }

    public function markAllMigrated(BundleInterface $bundle): void
    {
        $config = $this->getConfiguration($bundle);

        foreach ($config->getAvailableVersions() as $version) {
            if (!$config->hasVersionMigrated($version)) {
                $migrationResult = new ExecutionResult($version, Direction::UP);
                $config->getMetadataStorage()->complete($migrationResult);
            }
        }
    }

    public function getConfiguration(BundleInterface $bundle): BundleMigration
    {
        if (!isset($this->cacheConfigs[$bundle->getName()])) {
            $this->cacheConfigs[$bundle->getName()] = new BundleMigration($this->connection, $bundle);
        }

        return $this->cacheConfigs[$bundle->getName()];
    }
}
