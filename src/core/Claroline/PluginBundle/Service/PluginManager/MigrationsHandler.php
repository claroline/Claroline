<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Migration;

class MigrationsHandler
{
    private $dbal_connection;
    
    public function __construct(Connection $connection)
    {
        $this->setConnection($connection);
    }
    
    public function setConnection(Connection $connection)
    {
        $this->dbal_connection = $connection;
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
        $config = new Configuration($this->dbal_connection);
        $config->setName("{$plugin->getName()} Migration");
        $migration_path_pieces = array(
            $plugin->getPath(),
            'Migrations'
        );
        $migration_path = implode(DIRECTORY_SEPARATOR, $migration_path_pieces);
        $config->setMigrationsDirectory($migration_path);        
        $config->setMigrationsNamespace($plugin->getNamespace() . '\\Migrations');
        $config->registerMigrationsFromDirectory($migration_path);
        //FIXME next line is a hack fixing this bug 
        //@see https://github.com/doctrine/migrations/issues/47
        // hopefully we'll be able to remove the fix soon
        if( count($config->getMigrations()) == 0) return;
        $config->setMigrationsTableName(
            $plugin->getPrefix() . '_doctrine_migration_versions');
        
        $migration = new Migration($config);
        
        $migration->migrate($to);
        
    }
    
}