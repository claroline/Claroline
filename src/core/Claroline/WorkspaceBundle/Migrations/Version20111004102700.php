<?php

namespace Claroline\WorkspaceBundle\Migrations;

use Claroline\InstallBundle\Library\Migration\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20111004102700 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createWorkspaceTable($schema);
    }
    
    private function createWorkspaceTable(Schema $schema)
    {
        $table = $schema->createTable('claro_workspace');
        
        $this->addId($table);
        $table->addColumn('name', 'string', array('length' => 255));
    }
    
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_workspace');
    }
}