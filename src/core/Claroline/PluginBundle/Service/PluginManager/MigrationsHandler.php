<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Migration;
use Claroline\InstallBundle\Service\BundleMigrator;

class MigrationsHandler
{
    /* @var BundleMigrator */
    private $migrator;
    
    public function __construct(BundleMigrator $migrator) 
    {
        $this->migrator = $migrator;
    }
    
    public function install(ClarolinePlugin $plugin)
    {
        $this->migrator->createSchemaForBundle($plugin);
    }
    
    public function remove(ClarolinePlugin $plugin)
    {
        $this->migrator->dropSchemaForBundle($plugin);
    }
    
    public function migrate(ClarolinePlugin $plugin, $version)
    {        
        $this->migrator->migrateBundle($plugin, $version);
    }  
}