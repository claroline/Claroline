<?php

namespace Claroline\PluginBundle\Migrations;

use Claroline\InstallBundle\Library\Migration\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20111004102700 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createPluginTable($schema);
        $this->createToolTable($schema);
        $this->createApplicationTable($schema);
        $this->createApplicationLauncherTable($schema);
        $this->createLauncherRoleJoinTable($schema);
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
        $table->addColumn('discr', 'string', array('length' => 255));       
        
        $this->storeTable($table);
    }
    
    private function createApplicationTable(Schema $schema)
    {
        $table = $schema->createTable('claro_application');
        
        $this->addId($table);
        $table->addColumn('index_route', 'string', array('length' => 255));
        $table->addColumn('is_eligible_for_platform_index', 'boolean');
        $table->addColumn('is_platform_index', 'boolean');
        $table->addColumn('is_eligible_for_connection_target', 'boolean');
        $table->addColumn('is_connection_target', 'boolean');
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_plugin'), 
            array('id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
        
        $this->storeTable($table);
    }
    
    private function createApplicationLauncherTable(Schema $schema)
    {
        $table = $schema->createTable('claro_application_launcher');
        
        $this->addId($table);
        $table->addColumn('application_id', 'integer', array('notnull' => true));
        $table->addColumn('route_id', 'string', array('length' => 255));
        $table->addColumn('translation_key', 'string', array('length' => 255));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_application'),
            array('application_id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
    
    private function createLauncherRoleJoinTable(Schema $schema)
    {
        $table = $schema->createTable('claro_launcher_role');
        
        $table->addColumn('launcher_id', 'integer', array('notnull' => true));
        $table->addColumn('role_id', 'integer', array('notnull' => true));
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_application_launcher'),
            array('launcher_id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_role'),
            array('role_id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
    
    private function createToolTable(Schema $schema)
    {
        $table = $schema->createTable('claro_tool');
        
        $this->addId($table);
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_plugin'), 
            array('id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
    
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_launcher_role');
        $schema->dropTable('claro_application_launcher');
        $schema->dropTable('claro_application');
        $schema->dropTable('claro_tool');
        $schema->dropTable('claro_plugin');
    }
}