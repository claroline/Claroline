<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MigrationBundle\Manager;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Claroline\MigrationBundle\Generator\Generator;
use Claroline\MigrationBundle\Generator\Writer;
use Claroline\MigrationBundle\Migrator\Migrator;

/**
 * API entry point.
 */
class Manager
{
    private $generator;
    private $writer;
    private $migrator;
    private $logger;

    /**
     * Constructor.
     *
     * @param \Claroline\MigrationBundle\Generator\Generator    $generator
     * @param \Claroline\MigrationBundle\Generator\Writer       $writer
     * @param \Claroline\MigrationBundle\Migrator\Migrator      $migrator
     */
    public function __construct(
        Generator $generator,
        Writer $writer,
        Migrator $migrator
    )
    {
        $this->generator = $generator;
        $this->migrator = $migrator;
        $this->writer = $writer;
    }

    /**
     * Sets a logger for this class's operations.
     *
     * @param \Closure $logger
     */
    public function setLogger(\Closure $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Generates bundle migrations classes for all the available driver platforms.
     *
     * @param Symfony\Component\HttpKernel\Bundle\Bundle $bundle
     */
    public function generateBundleMigration(Bundle $bundle)
    {
        $platforms = $this->getAvailablePlatforms();
        $version = date('YmdHis');
        $this->log("Generating migrations classes for '{$bundle->getName()}'...");

        foreach ($platforms as $driverName => $platform) {
            $queries = $this->generator->generateMigrationQueries($bundle, $platform);

            if (count($queries[Generator::QUERIES_UP]) > 0 || count($queries[Generator::QUERIES_DOWN]) > 0) {
                $this->log(" - Generating migration class for {$driverName} driver...");
                $this->writer->writeMigrationClass($bundle, $driverName, $version, $queries);
            } else {
                $this->log('Nothing to generate: database and mapping are synced');
                break;
            }
        }
    }

    /**
     * Returns information about the migration status of a bundle. The return
     * value is the same than Migrator::getMigrationStatus().
     *
     * @param Symfony\Component\HttpKernel\Bundle\Bundle $bundle
     *
     * @return array
     */
    public function getBundleStatus(Bundle $bundle)
    {
        return $this->migrator->getMigrationStatus($bundle);
    }

    /**
     * Upgrades a bundle to a specified version. The version can be either an
     * explicit version string or a Migrator::VERSION_* constant.
     *
     * @param Symfony\Component\HttpKernel\Bundle\Bundle    $bundle
     * @param string                                        $version
     */
    public function upgradeBundle(Bundle $bundle, $version)
    {
        $this->doMigrate($bundle, $version, Migrator::DIRECTION_UP);
    }

    /**
     * Upgrades a bundle to a specified version. The version can be either an
     * explicit version string or a Migrator::VERSION_* constant.
     *
     * @param Symfony\Component\HttpKernel\Bundle\Bundle    $bundle
     * @param string                                        $version
     */
    public function downgradeBundle(Bundle $bundle, $version)
    {
        $this->doMigrate($bundle, $version, Migrator::DIRECTION_DOWN);
    }

    /**
     * Deletes migration classes which are above the current version of a bundle.
     *
     * @param string $bundleName
     */
    public function discardUpperMigrations(Bundle $bundle)
    {
        $drivers = array_keys($this->getAvailablePlatforms());
        $currentVersion = $this->migrator->getCurrentVersion($bundle);
        $this->log("Deleting migration classes above version {$currentVersion} for '{$bundle->getName()}'...");
        $hasDeleted = false;

        foreach ($drivers as $driver) {
            $deletedVersions = $this->writer->deleteUpperMigrationClasses($bundle, $driver, $currentVersion);

            if (count($deletedVersions) > 0) {
                $hasDeleted = true;

                foreach ($deletedVersions as $version) {
                    $this->log(" - Deleted {$version} for driver {$driver}");
                }
            }
        }

        if (!$hasDeleted) {
            $this->log('Nothing to discard: there are no migrations classes above the current version');
        }
    }

    /**
     * Returns the list of available driver platforms.
     *
     * Note: this method is public for testing purposes only
     *
     * @return array[AbstractPlatform]
     */
    public function getAvailablePlatforms()
    {
        $rDriverManager = new \ReflectionClass('Doctrine\DBAL\DriverManager');
        $rMap = $rDriverManager->getProperty('_driverMap');
        $rMap->setAccessible(true);
        $driverMap = $rMap->getValue();
        $platforms = array();

        foreach ($driverMap as $driverName => $driverClass) {
            $driver = new $driverClass;
            $platforms[$driverName] = $driver->getDatabasePlatform();
        }

        return $platforms;
    }

    private function doMigrate(Bundle $bundle, $version, $direction)
    {
        $action = $direction === Migrator::DIRECTION_UP ? 'Ugprading' : 'Downgrading';
        $this->log("{$action} bundle '{$bundle->getName()}'...");
        $queries = $this->migrator->migrate($bundle, $version, $direction);
        $currentVersion = $this->migrator->getCurrentVersion($bundle);
        $this->log(
            count($queries) === 0 ?
                "Nothing to execute: bundle is already at version {$currentVersion}" :
                "Done: bundle is now at version {$currentVersion}"
        );
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log($message);
        }
    }
}
