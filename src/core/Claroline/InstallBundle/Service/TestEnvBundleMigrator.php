<?php

namespace Claroline\InstallBundle\Service;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\DBAL\Connection;
use Claroline\InstallBundle\Library\Migration\BundleMigration;
use Claroline\InstallBundle\Library\Migration\MigrationHelper;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Migration;

class TestEnvBundleMigrator extends BundleMigrator
{
    
    public function createSchemaForBundle(Bundle $bundle)
    {
        parent:: createSchemaForBundle($bundle);
        $migration = $this->buildMigrationForBundle($bundle); 
        $migration && $migration->migrate(null);
    }
    
    public function dropSchemaForBundle(Bundle $bundle)
    {
        parent::dropSchemaForBundle($bundle);
        $migration = $this->buildMigrationForBundle($bundle); 
        $migration && $migration->migrate('');
    }
    
    public function migrateBundle(Bundle $bundle, $version = null)
    {
        parent :: migrateBundle($bundle, $version);
        $migration = $this->buildMigrationForBundle($bundle); 
        $migration && $migration->migrate($version);
    }
    
    private function buildMigrationForBundle(Bundle $bundle)
    {
        $config = new Configuration($this->connection);
        $config->setName("{$bundle->getName()} Migration For Test Env");
        $migrationPathPieces = array(
            $bundle->getPath(),
            'Tests',
            'Stub',
            'Migrations'
        );
        $migrationPath = implode(DIRECTORY_SEPARATOR, $migrationPathPieces);
        $config->setMigrationsDirectory($migrationPath);        
        $config->setMigrationsNamespace($bundle->getNamespace() . '\\Tests\\Stub\\Migrations');
        $config->registerMigrationsFromDirectory($migrationPath);
        
        //FIXME next lines are a hack fixing this bug 
        //@see https://github.com/doctrine/migrations/issues/47
        // hopefully we'll be able to remove the fix soon
        // ADDENDUM : this is fixed in PHP 5.4.0, should this fix be removed ?
        if( count($config->getMigrations()) == 0)
        {
            return;
        }
        $prefix = $this->migrationHelper->getTablePrefixForBundle($bundle);
        $config->setMigrationsTableName($prefix . '_tests_doctrine_migration_versions');
        return new Migration($config);
    }   
}