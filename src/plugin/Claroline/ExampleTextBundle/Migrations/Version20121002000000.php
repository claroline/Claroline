<?php

namespace Claroline\ExampleTextBundle\Migrations;

use Claroline\CoreBundle\Library\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

//BundleMigration is written on top of Doctrine\DBAL\Migrations\AbstractMigration and contains some helper methods.
//You can use the doctrine migration class aswell (see the doctrine doc).
class Version20121002000000 extends BundleMigration
{
    //will be fired at the plugin installation.
    public function up(Schema $schema)
    {
        $this->createMyTable($schema);
    }

    //will be fired at the plugin uninstallation.
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_example_text');
    }

    public function createMyTable(Schema $schema)
    {
        //table creation.
        $table = $schema->createTable('claro_example_text');

        //autonumber id.
        $this->addId($table);
        //adds a column.
        $table->addColumn('text', 'text');
    }
}