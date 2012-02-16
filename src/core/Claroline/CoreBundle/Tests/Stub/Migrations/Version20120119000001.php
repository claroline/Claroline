<?php

namespace Claroline\CoreBundle\Tests\Stub\Migrations;

use Claroline\CoreBundle\Library\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120119000001 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createTreeAncestorTable($schema);
        $this->createFirstChildTable($schema);
        $this->createSecondChildTable($schema);
    }
    
    private function createTreeAncestorTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_tree_ancestor');
        
        $this->addId($table);
        $table->addColumn('treeAncestorField', 'string', array('length' => 255));
        $table->addColumn('lft', 'integer');
        $table->addColumn('rgt', 'integer');
        $table->addColumn('lvl', 'integer');
        $table->addColumn('root', 'integer', array('notnull' => true));
        $table->addColumn('parent_id', 'integer', array('notnull' => false));
        $table->addColumn('discr', 'string', array('length' => 255));       
        
        $this->storeTable($table);
    }
    
    private function createFirstChildTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_node_first_child');
        
        $this->addId($table);
        $table->addColumn('firstChildField', 'string', array('length' => 255));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_test_tree_ancestor'), 
            array('id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
    
    private function createSecondChildTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_node_second_child');
        
        $this->addId($table);
        $table->addColumn('secondChildField', 'string', array('length' => 255));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_test_tree_ancestor'), 
            array('id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
    
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_test_node_second_child');
        $schema->dropTable('claro_test_node_first_child');
        $schema->dropTable('claro_test_tree_ancestor');
    }
}