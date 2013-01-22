<?php

namespace Claroline\CoreBundle\Tests\Stub\Migrations;

use Claroline\CoreBundle\Library\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120119000003 extends BundleMigration
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

        $this->addId($table);
        $table->addColumn('firstEntityField', 'string', array('length' => 255));
        $table->addColumn('discr', 'string', array('length' => 255));

        $this->storeTable($table);
    }

    private function createFirstEntityChildTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_security_first_entity_child');

        $this->addId($table);
        $table->addColumn('firstEntityChildField', 'string', array('length' => 255));
        $table->addForeignKeyConstraint(
            $this->getStoredTable('claro_test_security_first_entity'),
            array('id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
    }

    private function createSecondEntityTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_security_second_entity');

        $this->addId($table);
        $table->addColumn('secondChildField', 'string', array('length' => 255));
        $table->setPrimaryKey(array('id'));
    }

    private function createThirdEntityTable(Schema $schema)
    {
        $table = $schema->createTable('claro_test_security_third_entity');

        $this->addId($table);
        $table->addColumn('thirdChildField', 'string', array('length' => 255));
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('claro_test_security_first_entity_child');
        $schema->dropTable('claro_test_security_first_entity');
        $schema->dropTable('claro_test_security_second_entity');
        $schema->dropTable('claro_test_security_third_entity');
    }
}
