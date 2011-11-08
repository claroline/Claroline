<?php

namespace Claroline\WorkspaceBundle\Migrations;

use Claroline\InstallBundle\Library\Migration\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20111004102700 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createWorkspaceTable($schema);
        $this->createWorkspaceToolTable($schema);
    }
    
    private function createWorkspaceTable(Schema $schema)
    {
        $table = $schema->createTable('claro_workspace');
        $this->addId($table);
        
        $table->addColumn('name', 'string', array('length' => 255));       
        
        $this->storeTable($table);
    }
    
    private function createWorkspaceToolTable(Schema $schema)
    {
        $table = $schema->createTable('claro_workspace_tool');
        
        $table->addColumn('workspace_id', 'integer', array('notnull' => true));
        $table->addColumn('tool_id', 'integer', array('notnull' => true));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_workspace'),
            array('workspace_id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_tool'),
            array('tool_id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
    
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_workspace_tool');
        $schema->dropTable('claro_workspace');
    }
}