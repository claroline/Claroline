<?php

namespace Claroline\CoreBundle\Migrations;

use Claroline\CoreBundle\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120119000000 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createUserTable($schema);
        $this->createRoleTable($schema);
        $this->createUserRoleTable($schema);
        $this->createWorkspaceTable($schema);
        $this->createUserWorkspaceRoleTable($schema);
        $this->createResourceTable($schema);
        $this->createTextTableSchema($schema);
        $this->createPluginTable($schema);
        $this->createToolTable($schema);
        $this->createToolInstanceTable($schema);
        $this->createExtensionTable($schema);
    }   
    
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_extension');
        $schema->dropTable('claro_tool_instance');
        $schema->dropTable('claro_tool');
        $schema->dropTable('claro_plugin');
        $schema->dropTable('claro_text');
        $schema->dropTable('claro_resource');
        $schema->dropTable('claro_user_workspace_role');
        $schema->dropTable('claro_workspace');
        $schema->dropTable('claro_user_role');
        $schema->dropTable('claro_role');
        $schema->dropTable('claro_user');
    }
    
    private function createUserTable(Schema $schema)
    {
        $table = $schema->createTable('claro_user');
        
        $this->addId($table);       
        $table->addColumn('first_name', 'string', array('length' => 50));
        $table->addColumn('last_name', 'string', array('length' => 50));
        $table->addColumn('username', 'string', array('length' => 255));
        $table->addColumn('password', 'string', array('length' => 255));
        $table->addColumn('salt', 'string', array('length' => 255));
        $table->addUniqueIndex(array('username'));
        
        $this->storeTable($table);
    }
    
    private function createRoleTable(Schema $schema)
    {
        $table = $schema->createTable('claro_role');
        
        $this->addId($table);
        $this->addDiscriminator($table);
        $table->addColumn('name', 'string', array('length' => 255));
        $table->addColumn('workspace_id', 'integer', array('notnull' => false));
        $table->addUniqueIndex(array('name'));
        
        $this->storeTable($table);
    }
    
    private function createUserRoleTable(Schema $schema)
    {
        $table = $schema->createTable('claro_user_role');

        $table->addColumn('user_id', 'integer', array('notnull' => true));
        $table->addColumn('role_id', 'integer', array('notnull' => true));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_user'),
            array('user_id'),
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
    
    private function createWorkspaceTable(Schema $schema)
    {
        $table = $schema->createTable('claro_workspace');
        
        $this->addId($table);
        $table->addColumn('name', 'string', array('length' => 255));
        
        $this->storeTable($table);
    }
    
    private function createUserWorkspaceRoleTable(Schema $schema)
    {
        $table = $schema->createTable('claro_user_workspace_role');

        $table->addColumn('user_id', 'integer', array('notnull' => true));
        $table->addColumn('workspace_role_id', 'integer', array('notnull' => true));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_user'),
            array('user_id'),
            array('id'),
            array("onDelete" => "CASCADE")
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_role'),
            array('workspace_role_id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
    
    private function createResourceTable(Schema $schema)
    {
        $table = $schema->createTable('claro_resource');
        
        $this->addId($table);
        $this->addDiscriminator($table);
        $table->addColumn('created', 'datetime');
        $table->addColumn('updated', 'datetime');
        
        $this->storeTable($table);
    }
    
    private function createTextTableSchema(Schema $schema)
    {
        $table = $schema->createTable('claro_text');
        
        $this->addId($table);
        $this->addDiscriminator($table);
        $table->addColumn('type', 'string', array('length' => 255));
        $table->addColumn('content', 'text');
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_resource'),
            array('id'),
            array('id'),
            array("onDelete" => "CASCADE")
        );
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
}