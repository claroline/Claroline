<?php

namespace Claroline\ResourceBundle\Migrations;

use Claroline\InstallBundle\Library\Migration\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20111004102700 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createResourceTable($schema);
        $this->createTextTableSchema($schema);
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
        $table->addColumn('type', 'string', array('length' => 255));
        $table->addColumn('content', 'text');
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_resource'),
            array('id'),
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
    
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_text');
        $schema->dropTable('claro_resource');
    }
}