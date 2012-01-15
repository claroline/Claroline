<?php

namespace Claroline\WorkspaceBundle\Migrations;

use Claroline\InstallBundle\Library\Migration\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20111004102700 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createWorkspaceTable($schema);
        $this->createWorkspaceUserTable($schema);
    }
    
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_workspace_user');
        $schema->dropTable('claro_workspace');
    }
    
    private function createWorkspaceTable(Schema $schema)
    {
        $table = $schema->createTable('claro_workspace');
        
        $this->addId($table);
        $table->addColumn('name', 'string', array('length' => 255));
        
        $this->storeTable($table);
    }
    
    private function createWorkspaceUserTable(Schema $schema)
    {
        $table = $schema->createTable('claro_workspace_user');
        
        $this->addId($table);
        $table->addColumn('workspace_id', 'integer', array('notnull' => true));
        $table->addColumn('user_id', 'integer', array('notnull' => true));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_workspace'),
            array('workspace_id'),
            array('id'),
            array("onDelete" => "CASCADE")
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_user'),
            array('user_id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
}