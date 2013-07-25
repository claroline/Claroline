<?php

namespace Claroline\MigrationBundle\Library;

use Symfony\Component\HttpKernel\Kernel;

class Manager
{
    private $kernel;
    private $generator;
    private $writer;
    private $logger;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\HttpKernel\Kernel $kernel
     * @param \Claroline\MigrationBundle\Library\Generator $generator
     * @param \Claroline\MigrationBundle\Library\Writer $writer
     */
    public function __construct(Kernel $kernel, Generator $generator, Writer $writer)
    {
        $this->kernel = $kernel;
        $this->generator = $generator;
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
        $this->log("Generating migrations classes for '{$bundleName}'...");

        foreach ($platforms as $driverName => $platform) {
            $this->log(" - Generating migration class for {$driverName} driver...");
            $queries = $this->generator->generateMigrationQueries($bundle, $platform);
            $this->writer->writeMigrationClass($bundle, $driverName, '123', $queries);
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

    private function log($message)
    {
        if ($log = $this->logger) {
            $log($message);
        }
    }
}
