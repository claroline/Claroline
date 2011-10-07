<?php

namespace Claroline\InstallBundle\Service;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\DBAL\Connection;
use Claroline\InstallBundle\Library\Migration\BundleMigration;
use Claroline\InstallBundle\Library\Migration\MigrationHelper;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Migration;

class BundleMigrator
{
    /* @var Connection */
    protected $connection;
    
    /* @var MigrationHelper */
    protected $migrationHelper;
    
    public function __construct(Connection $connection, MigrationHelper $helper)
    {
        $this->connection = $connection;
        $this->migrationHelper = $helper;
    }
    
    public function createSchemaForBundle(Bundle $bundle)
    {
        $migration = $this->buildMigrationForBundle($bundle); 
        $migration && $migration->migrate(null);
    }
    
    public function dropSchemaForBundle(Bundle $bundle)
    {
        $migration = $this->buildMigrationForBundle($bundle); 
        $migration && $migration->migrate('');
    }
    
    public function migrateBundle(Bundle $bundle, $version = null)
    {
        $migration = $this->buildMigrationForBundle($bundle); 
        $migration && $migration->migrate($version);
    }
    
    private function buildMigrationForBundle(Bundle $bundle)
    {
        $config = new Configuration($this->connection);
        $config->setName("{$bundle->getName()} Migration");
        $migrationPathPieces = array(
            $bundle->getPath(),
            'Migrations'
        );
        $migrationPath = implode(DIRECTORY_SEPARATOR, $migrationPathPieces);
        $config->setMigrationsDirectory($migrationPath);        
        $config->setMigrationsNamespace($bundle->getNamespace() . '\\Migrations');
        $config->registerMigrationsFromDirectory($migrationPath);
        //FIXME next lines are a hack fixing this bug 
        //@see https://github.com/doctrine/migrations/issues/47
        // hopefully we'll be able to remove the fix soon
        if ( count($config->getMigrations()) == 0)
        {
            return;
        }
        $prefix = $this->migrationHelper->getTablePrefixForBundle($bundle);
        $config->setMigrationsTableName($prefix . '_doctrine_migration_versions');
        
        return new Migration($config);
    }
}
