<?php
namespace Claroline\CommonBundle\Tests\Stub\Migrations;

use Claroline\InstallBundle\Library\Migration\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20111007102700 extends BundleMigration
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
        $this->addDiscriminator($table);        
        $this->addId($table);
        $this->addReference($table, 'parent', true);
        
        $table->addColumn('treeAncestorField', 'string', array('length' => 255));
        $table->addColumn('lft', 'integer');
        $table->addColumn('rgt', 'integer');
        $table->addColumn('lvl', 'integer');
        $table->addColumn('root', 'integer', array('nullable' => true));
        
    }
    
    private function createFirstChildTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_node_first_child');
        $this->addId($table, false);        
        
        $table->addColumn('firstChildField', 'string', array('length' => 255));
    }
    
    private function createSecondChildTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_node_second_child');
        $this->addId($table, false);        
        
        $table->addColumn('secondChildField', 'string', array('length' => 255));
    }
    

    public function down(Schema $schema)
    {
        $schema->dropTable('claro_test_node_second_child');
        $schema->dropTable('claro_test_node_first_child');
        $schema->dropTable('claro_test_tree_ancestor');
    }


}
