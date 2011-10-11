<?php
namespace Claroline\PluginBundle\Migrations;

use Claroline\InstallBundle\Library\Migration\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20111004102700 extends BundleMigration
{  
    public function up(Schema $schema)
    {
        $this->createPluginTable($schema);
        $this->createApplicationTable($schema);
        $this->createApplicationLauncherTable($schema);
        $this->createLauncherRoleJoinTable($schema);
        $this->createToolTable($schema);
    }
    
    private function createPluginTable(Schema $schema)
    {
        $table = $schema->createTable('claro_plugin');
        $this->addId($table);
        
        $table->addColumn('type', 'string', array('length' => 255));
        $table->addColumn('bundle_fqcn', 'string', array('length' => 255));
        $table->addColumn('vendor_name', 'string', array('length' => 50));
        $table->addColumn('short_name', 'string', array('length' => 50));
        $table->addColumn('name_translation_key', 'string', array('length' => 255));
        $table->addColumn('description', 'string', array('length' => 255));
        $table->addColumn('discr', 'string');
    }
    
    private function createApplicationTable(Schema $schema)
    {
        $table = $schema->createTable('claro_application');
        $this->addId($table);
    }
    
    private function createApplicationLauncherTable(Schema $schema)
    {
        $table = $schema->createTable('claro_application_launcher');
        $this->addId($table);
        
        $this->addReference($table, 'application');
        $table->addColumn('route_id', 'string', array('length' => 255));
        $table->addColumn('translation_key', 'string', array('length' => 255)); 
    }
    
    private function createLauncherRoleJoinTable(Schema $schema)
    {
        $table = $schema->createTable('claro_launcher_role');
        $this->addReference($table, 'launcher');
        $this->addReference($table, 'role');
    }
    
    private function createToolTable(Schema $schema)
    {
        $table = $schema->createTable('claro_tool');
        $this->addId($table);
    }
    
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_plugin');
        $schema->dropTable('claro_application');
        $schema->dropTable('claro_application_launcher');
        $schema->dropTable('claro_launcher_role');
        $schema->dropTable('claro_tool');
    }
}