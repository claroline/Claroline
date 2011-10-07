<?php
namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\Tests\PluginBundleTestCase;
use Claroline\PluginBundle\Service\PluginManager\MigrationsHandler;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;


class MigrationsHandlerTest extends PluginBundleTestCase
{
    
    /** @return Connection */
    public function getConnection()
    {
        return $this->client->getConnection();
    }
    
    /** @return AbstractSchemaManager */
    public function getSchemaManager()
    {
        return $this->getConnection()->getSchemaManager();
    }
    
    public function testVersionsTableIsCreatedAndPopulatedOnInstall()
    {
        $pluginFQCN = 'Valid\WithMigrations\ValidWithMigrations';
        $plugin = $this->build_plugin($pluginFQCN);

        $this->migrationsHandler->install($plugin);
        
        $query = "SELECT * FROM valid_withmigrations_doctrine_migration_versions";
        $result = $this->getConnection()->fetchAll($query);
        
        $this->assertEquals(2, count($result));
        
        // unfortuntaly create table are not rollback-able
        $this->migrationsHandler->remove($plugin);
    }
    
    public function testMigrationsAreEffectivelyRunOnInstall()
    {
        $pluginFQCN = 'Valid\WithMigrations\ValidWithMigrations';
        $plugin = $this->build_plugin($pluginFQCN);

        $this->migrationsHandler->install($plugin);
        
        $schema = $this->getSchemaManager()->createSchema();
        $table = $schema->getTable('valid_withmigrations_stuffs');
        
        $this->assertTrue($table->hasColumn('id'));
        $this->assertTrue($table->hasColumn('name'));
        $this->assertTrue($table->hasColumn('last_modified'));
        
        // unfortuntaly create table are not rollback-able
        $this->migrationsHandler->remove($plugin);
        
    }
    
    public function testVersionsTableIsCreatedAndEmptiedOnRemove()
    {
        $pluginFQCN = 'Valid\WithMigrations\ValidWithMigrations';
        $plugin = $this->build_plugin($pluginFQCN);

        $this->migrationsHandler->install($plugin);
        $this->migrationsHandler->remove($plugin);
        
        $query = "SELECT * FROM valid_withmigrations_doctrine_migration_versions";
        $result = $this->getConnection()->fetchAll($query);
        
        $this->assertEquals(0, count($result));
    }
    
    public function testMigrationsAreEffectivelyRunOnRemove()
    {
        $pluginFQCN = 'Valid\WithMigrations\ValidWithMigrations';
        $plugin = $this->build_plugin($pluginFQCN);

        $this->migrationsHandler->install($plugin);
        $this->migrationsHandler->remove($plugin);
        
        $schema = $this->getSchemaManager()->createSchema();
        $this->assertFalse($schema->hasTable('valid_withmigrations_stuffs'));
                
    }
    
    public function testVersionsTableIsPopulatedOnUpgrade()
    {
        $pluginFQCN = 'Valid\WithMigrations\ValidWithMigrations';
        $plugin = $this->build_plugin($pluginFQCN);

        $this->migrationsHandler->migrate($plugin, '00000000000001');
                
        $query = "SELECT * FROM valid_withmigrations_doctrine_migration_versions";
        $result = $this->getConnection()->fetchAll($query);        
        $this->assertEquals(1, count($result));
        
        $this->migrationsHandler->install($plugin);
        
        $result = $this->getConnection()->fetchAll($query);        
        $this->assertEquals(2, count($result));
        
        // unfortuntaly create table are not rollback-able
        $this->migrationsHandler->remove($plugin);
    }
    
    public function testMigrationsAreEffectivelyRunInRightOrderOnUpgrade()
    {
        $pluginFQCN = 'Valid\WithMigrations\ValidWithMigrations';
        $plugin = $this->build_plugin($pluginFQCN);

        $this->migrationsHandler->migrate($plugin, '00000000000001');
        $schema = $this->getSchemaManager()->createSchema();
        $table = $schema->getTable('valid_withmigrations_stuffs');
        $this->assertTrue($table->hasColumn('id'));
        $this->assertTrue($table->hasColumn('name'));
        $this->assertFalse($table->hasColumn('last_modified'));
        
        $this->migrationsHandler->install($plugin);
        
        $schema = $this->getSchemaManager()->createSchema();
        $table = $schema->getTable('valid_withmigrations_stuffs');
        $this->assertTrue($table->hasColumn('last_modified'));
        
        // unfortuntaly create table are not rollback-able
        $this->migrationsHandler->remove($plugin);
    }
}