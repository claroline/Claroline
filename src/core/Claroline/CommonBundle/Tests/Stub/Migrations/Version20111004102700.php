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
        $this->addDiscriminator($table);        
        $this->addId($table);        
        
        $table->addColumn('ancestorField', 'string', array('length' => 255));
    }
    
    private function createFirstChildTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_firstchild');
        $this->addId($table, false);        
        
        $table->addColumn('firstChildField', 'string', array('length' => 255));
    }
    
    private function createSecondChildTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_secondchild');
        $this->addId($table, false);        
        
        $table->addColumn('secondChildField', 'string', array('length' => 255));
    }
    
    private function createFirstDescendantTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_firstdescendant');
        $this->addId($table, false);        
        
        $table->addColumn('firstDescendantField', 'string', array('length' => 255));
    }
    
    private function createSecondDescendantTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_seconddescendant');
        $this->addId($table, false);        
        
        $table->addColumn('secondDescendantField', 'string', array('length' => 255));
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
