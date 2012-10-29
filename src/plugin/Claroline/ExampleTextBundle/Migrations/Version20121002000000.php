<?php

namespace Claroline\ExampleTextBundle\Migrations;

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
        $this->createExampleTextTable($schema);
    }

    /**
     * Will be fired at the plugin uninstallation.
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_example_text');
    }

    /**
     * Create the 'claro_example_text' table.
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function createExampleTextTable(Schema $schema)
    {
        // Table creation
        $table = $schema->createTable('claro_example_text');
        // Add an auto increment id
        $this->addId($table);
        // Add a column
        $table->addColumn('text', 'text');
    }

}