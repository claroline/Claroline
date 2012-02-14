<?php

namespace Claroline\CoreBundle\Tests\Stub\Migrations;

use Claroline\CoreBundle\Library\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120119000000 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createAncestorTable($schema);
        $this->createFirstChildTable($schema);
        $this->createSecondChildTable($schema);
        $this->createFirstDescendantTable($schema);
        $this->createSecondDescendantTable($schema);
    }
    
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_test_seconddescendant');
        $schema->dropTable('claro_test_firstdescendant');
        $schema->dropTable('claro_test_secondchild');
        $schema->dropTable('claro_test_firstchild');
        $schema->dropTable('claro_test_ancestor');
    }
    
    private function createAncestorTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_ancestor');
        $this->addId($table);
        $table->addColumn('ancestorField', 'string', array('length' => 255));
        $table->addColumn('discr', 'string', array('length' => 255));       
        
        
        $this->storeTable($table);       
    }
    
    private function createFirstChildTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_firstchild');
        
        $this->addId($table);
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

        $this->addId($table);
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
        
        $this->addId($table);
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
        
        $this->addId($table);
        $table->addColumn('secondDescendantField', 'string', array('length' => 255));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_test_ancestor'), 
            array('id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
}