<?php

namespace Claroline\ExampleBundle\Migrations;

use Claroline\CoreBundle\Library\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 *  BundleMigration is written on top of Doctrine\DBAL\Migrations\AbstractMigration
 *  and contains some helper methods.
 *  You can use the doctrine migration class as well (see the doctrine doc).
 */
class Version20121002000000 extends BundleMigration
{

    /**
     * Will be fired at the plugin installation.
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->createExampleTable($schema);
    }

    /**
     * Will be fired at the plugin uninstallation.
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_example');
    }

    /**
     * Create the 'claro_example_text' table.
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function createExampleTable(Schema $schema)
    {
        // Table creation
        $table = $schema->createTable('claro_example');
        // Add an auto increment id
        $this->addId($table);
        // Add a column
        $table->addColumn('text', 'text');
        // Delete the Example resource on claro_example table when it's deleted on claro_resource table
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_resource'), array('id'), array('id'), array("onDelete" => "CASCADE")
        );
    }
}