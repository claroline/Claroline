<?php

namespace Claroline\MigrationBundle\Library;

use Symfony\Component\HttpKernel\Kernel;

/**
 * API entry point.
 */
class Manager
{
    private $kernel;
    private $generator;
    private $writer;
    private $migrator;
    private $logger;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\HttpKernel\Kernel          $kernel
     * @param \Claroline\MigrationBundle\Library\Generator  $generator
     * @param \Claroline\MigrationBundle\Library\Writer     $writer
     * @param \Claroline\MigrationBundle\Library\Migrator   $migrator
     */
    public function __construct(
        Kernel $kernel,
        Generator $generator,
        Writer $writer,
        Migrator $migrator
    )
    {
        $this->kernel = $kernel;
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
     * @param string $bundleName
     */
    public function generateBundleMigration($bundleName)
    {
        $bundle = $this->kernel->getBundle($bundleName);
        $platforms = $this->getAvailablePlatforms();
        $version = date('YmdHis');
        $this->log("Generating migrations classes for '{$bundleName}'...");

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
     * @param string $bundleName
     *
     * @return array
     */
    public function getBundleStatus($bundleName)
    {
        $bundle = $this->kernel->getBundle($bundleName);

        return $this->migrator->getMigrationStatus($bundle);
    }

    /**
     * Upgrades a bundle to a specified version. The version can be either an
     * explicit version string or a Migrator::VERSION_* constant.
     *
     * @param string $bundleName
     * @param string $version
     */
    public function upgradeBundle($bundleName, $version)
    {
        $this->doMigrate($bundleName, $version, Migrator::DIRECTION_UP);
    }

    /**
     * Upgrades a bundle to a specified version. The version can be either an
     * explicit version string or a Migrator::VERSION_* constant.
     *
     * @param string $bundleName
     * @param string $version
     */
    public function downgradeBundle($bundleName, $version)
    {
        $this->doMigrate($bundleName, $version, Migrator::DIRECTION_DOWN);
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

    private function doMigrate($bundleName, $version, $direction)
    {
        $bundle = $this->kernel->getBundle($bundleName);
        $action = $direction === Migrator::DIRECTION_UP ? 'Ugprading' : 'Downgrading';
        $this->log("{$action} bundle '{$bundleName}'...");
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
