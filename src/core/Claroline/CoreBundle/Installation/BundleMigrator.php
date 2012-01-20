<?php

namespace Claroline\CoreBundle\Installation;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Claroline\CoreBundle\Installation\MigrationBuilder;

class BundleMigrator
{
    protected $migrationBuilder;
    
    public function __construct(MigrationBuilder $builder)
    {
        $this->migrationBuilder = $builder;
    }
    
    public function createSchemaForBundle(Bundle $bundle)
    {
        $migrations = $this->migrationBuilder->buildMigrationsForBundle($bundle);
        
        foreach ($migrations as $migration)
        {
            $migration->migrate(null);
        }
    }
    
    public function dropSchemaForBundle(Bundle $bundle)
    {
        $migrations = $this->migrationBuilder->buildMigrationsForBundle($bundle);
        
        foreach ($migrations as $migration)
        {
            $migration->migrate('0');
        }
    }
    
    public function migrateBundle(Bundle $bundle, $version = null)
    {        
        $migrations = $this->migrationBuilder->buildMigrationsForBundle($bundle);
        
        foreach ($migrations as $migration)
        {
            $migration->migrate($version);
        }
    }
}