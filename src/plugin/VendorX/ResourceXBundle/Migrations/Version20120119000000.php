<?php

namespace VendorX\ResourceXBundle\Migrations;

use Claroline\CoreBundle\Library\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120119000000 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createResourceXTable($schema);
        $this->createResourceYTable($schema);
    }
    
    public function down(Schema $schema)
    {
        $schema->dropTable('vx_resource_x');
        $schema->dropTable('vx_resource_y');
    }
    
    private function createResourceXTable(Schema $schema)
    {
        $table = $schema->createTable('vx_resource_x');
        
        $this->addId($table);        
        $table->addColumn('some_field', 'string', array('length' => 255));
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_resource'),
            array('id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
    
    private function createResourceYTable(Schema $schema)
    {
        $table = $schema->createTable('vx_resource_y');
        
        $this->addId($table);        
        $table->addColumn('some_field', 'string', array('length' => 255));
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_resource'),
            array('id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
}