<?php

namespace Innova\PathBundle\Migrations;

use Claroline\CoreBundle\Library\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 *  BundleMigration is written on top of Doctrine\DBAL\Migrations\AbstractMigration
 *  and contains some helper methods.
 *  You can use the doctrine migration class as well (see the doctrine doc).
 */
class Version20130716102556 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createPathTable($schema);
        $this->createOperatorTypeTable($schema);
        $this->createOperatorTypeActivityTable($schema);
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    	$schema->dropTable('innova_path');
    	$schema->dropTable('innova_operatortype');
    	$schema->dropTable('innova_operatortype_activity');
    }

    /**
     * Create the 'innova_path' table.
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    private function createPathTable(Schema $schema)
    {
        // Table creation
        $table = $schema->createTable('innova_path');
        
        // Add an auto increment id
        $this->addId($table);
        
        // Add a column
        $table->addColumn(
        	'name', 
        	'string'
        );

        $table->addColumn(
        	'description', 
        	'text'
        );

        $table->addColumn(
        	'pattern', 
        	'boolean'
        );

        $table->addColumn(
        	'slug', 
        	'string', 
        	array(
        		'length' => 128, 
        		'unique' => true
        	)
        );

        $table->addColumn(
        	'created', 
        	'datetime'
        );

        $table->addColumn(
        	'updated', 
        	'datetime'
        );

        $table->addColumn(
        	'workspace_id', 
        	'integer', 
        	array('notnull' => false)
        );

        // Delete the Example resource on claro_example table when it's deleted on claro_resource table
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_workspace'), 
            array('workspace_id'), 
            array('id'), 
            array('onDelete' => 'CASCADE')
        );
    }

     /**
     * Create the 'innova_operatortype' table.
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    private function createOperatorTypeTable(Schema $schema)
    {
        // Table creation
        $table = $schema->createTable('innova_operatortype');
        
        // Add an auto increment id
        $this->addId($table);
        
        // Add a column
        $table->addColumn(
        	'name', 
        	'string'
        );

        $table->addColumn(
        	'description', 
        	'text'
        );
    }

     /**
     * Create the 'innova_operatortype_activity' table.
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    private function createOperatorTypeActivityTable(Schema $schema)
    {
        // Table creation
        $table = $schema->createTable('innova_operatortype_activity');
        
        // Add an auto increment id
        $this->addId($table);

        // Add a column
        $table->addColumn(
        	'operatortype_id', 
        	'integer'
        );

         // Add a column
        $table->addColumn(
        	'activity_id', 
        	'integer'
        );
        $table->addUniqueIndex(array('activity_id'));
    }
}
