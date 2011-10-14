<?php

namespace Claroline\CommonBundle\Tests\Stub\Migrations;

use Claroline\InstallBundle\Library\Migration\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20111004102700 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createAncestorTable($schema);
        $this->createFirstChildTable($schema);
        $this->createSecondChildTable($schema);
        $this->createFirstDescendantTable($schema);
        $this->createSecondDescendantTable($schema);
    }
    
    private function createAncestorTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_ancestor');
        
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('ancestorField', 'string', array('length' => 255));
        $table->addColumn('discr', 'string', array('length' => 255));       
        $table->setPrimaryKey(array('id'));
        
        $this->storeTable($table);       
    }
    
    private function createFirstChildTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_firstchild');
        
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('firstChildField', 'string', array('length' => 255));        
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_test_ancestor'), 
            array('id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
    
    private function createSecondChildTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_secondchild');

        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('secondChildField', 'string', array('length' => 255));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_test_ancestor'), 
            array('id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
    
    private function createFirstDescendantTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_firstdescendant');
        
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('firstDescendantField', 'string', array('length' => 255));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_test_ancestor'), 
            array('id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
    
    private function createSecondDescendantTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_seconddescendant');
        
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('secondDescendantField', 'string', array('length' => 255));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_test_ancestor'), 
            array('id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
    
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_test_seconddescendant');
        $schema->dropTable('claro_test_firstdescendant');
        $schema->dropTable('claro_test_secondchild');
        $schema->dropTable('claro_test_firstchild');
        $schema->dropTable('claro_test_ancestor');
    }
}
