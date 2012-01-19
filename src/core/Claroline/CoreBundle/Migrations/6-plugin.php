<?php

namespace Claroline\CoreBundle\Migrations;

use Claroline\InstallBundle\Library\Migration\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20111004102700 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createPluginTable($schema);
        $this->createToolTable($schema);
        $this->createToolInstanceTable($schema);
        $this->createExtensionTable($schema);
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
        
        $this->storeTable($table);
    }
    
    private function createToolInstanceTable(Schema $schema)
    {
        $table = $schema->createTable('claro_tool_instance');
        
        $this->addId($table);
        $table->addColumn('tool_id', 'integer', array('notnull' => true));
        $table->addColumn('workspace_id', 'integer', array('notnull' => true));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_tool'), 
            array('tool_id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_workspace'),
            array('workspace_id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
    
    private function createExtensionTable(Schema $schema)
    {
        $table = $schema->createTable('claro_extension');
        
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->setPrimaryKey(array('id'));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_plugin'), 
            array('id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
    
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_extension');
        $schema->dropTable('claro_tool_instance');
        $schema->dropTable('claro_tool');
        $schema->dropTable('claro_plugin');
    }
}