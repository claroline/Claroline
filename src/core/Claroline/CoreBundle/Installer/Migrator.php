<?php

namespace Claroline\CoreBundle\Installer;

use Claroline\CoreBundle\Service\BundleMigrator;
use Claroline\CoreBundle\AbstractType\ClarolinePlugin;

class Migrator
{
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