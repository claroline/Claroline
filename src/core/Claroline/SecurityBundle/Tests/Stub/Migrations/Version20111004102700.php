<?php

namespace Claroline\SecurityBundle\Tests\Stub\Migrations;

use Claroline\InstallBundle\Library\Migration\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20111004102700 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createFirstEntityTable($schema);
        $this->createFirstEntityChildTable($schema);
        $this->createSecondEntityTable($schema);
        $this->createThirdEntityTable($schema);
    }
    
    private function createFirstEntityTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_security_first_entity');
        
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('firstEntityField', 'string', array('length' => 255));
        $table->addColumn('discr', 'string', array('length' => 255));       
        $table->setPrimaryKey(array('id'));
        
        $this->storeTable($table);  
    }
    
    private function createFirstEntityChildTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_security_first_entity_child');
        
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('firstEntityChildField', 'string', array('length' => 255));        
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_test_security_first_entity'), 
            array('id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
    
    private function createSecondEntityTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_security_second_entity');
        
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('secondChildField', 'string', array('length' => 255));  
        $table->setPrimaryKey(array('id'));
    }
    
    private function createThirdEntityTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_security_third_entity');
        
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('thirdChildField', 'string', array('length' => 255));  
        $table->setPrimaryKey(array('id'));
    }
    
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_test_security_first_entity_child');
        $schema->dropTable('claro_test_security_first_entity');
        $schema->dropTable('claro_test_security_second_entity');
        $schema->dropTable('claro_test_security_third_entity');
    }
}
