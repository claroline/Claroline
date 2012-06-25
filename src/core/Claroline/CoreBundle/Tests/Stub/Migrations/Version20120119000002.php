<?php

namespace Claroline\CoreBundle\Tests\Stub\Migrations;

use Claroline\CoreBundle\Library\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120119000002 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createSpecificResource1Table($schema);
        $this->createSpecificResource2Table($schema);
    }

    private function createSpecificResource1Table(Schema $schema)
    {
        $table = $schema->createTable('claro_test_specific_resource_1');
        $this->addId($table);
        $table->addColumn('some_field', 'string', array('length' => 255));
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_resource'), array('id'), array('id'), array("onDelete" => "CASCADE")
        );
    }

    private function createSpecificResource2Table(Schema $schema)
    {
        $table = $schema->createTable('claro_test_specific_resource_2');
        $this->addId($table);
        $table->addColumn('some_field', 'string', array('length' => 255));
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_resource'), array('id'), array('id'), array("onDelete" => "CASCADE")
        );
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('claro_test_specific_resource_1');
        $schema->dropTable('claro_test_specific_resource_2');
    }
}