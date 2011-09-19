<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Migration;

class MigrationsHandler
{
    private $connection;
    
    public function __construct(Connection $connection)
    {
        $this->setConnection($connection);
    }
    
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }


    public function install(ClarolinePlugin $plugin)
    {
        $this->migrate($plugin, null);
    }
    
    public function remove(ClarolinePlugin $plugin)
    {
        $this->migrate($plugin, '');
    }
    
    public function migrate(ClarolinePlugin $plugin, $to)
    {
        $config = new Configuration($this->connection);
        $config->setName("{$plugin->getName()} Migration");
        $migrationPathPieces = array(
            $plugin->getPath(),
            'Migrations'
        );
        $migrationPath = implode(DIRECTORY_SEPARATOR, $migrationPathPieces);
        $config->setMigrationsDirectory($migrationPath);        
        $config->setMigrationsNamespace($plugin->getNamespace() . '\\Migrations');
        $config->registerMigrationsFromDirectory($migrationPath);
        
        //FIXME next lines are a hack fixing this bug 
        //@see https://github.com/doctrine/migrations/issues/47
        // hopefully we'll be able to remove the fix soon
        if( count($config->getMigrations()) == 0)
        {
            return;
        }
        $config->setMigrationsTableName(
            $plugin->getPrefix() . '_doctrine_migration_versions'
        );
        
        $migration = new Migration($config);
        
        $migration->migrate($to);
        
    }
    
}