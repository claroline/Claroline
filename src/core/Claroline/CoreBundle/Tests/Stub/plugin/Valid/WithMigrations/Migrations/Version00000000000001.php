<?php

namespace Valid\WithMigrations\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Claroline\CoreBundle\Library\Installation\BundleMigration;

class Version00000000000001 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->createTable($this->getTablePrefix() . '_stuffs');

        $this->addId($table);
        $table->addColumn(
            'name',
            'string',
            array(
                'length' => 50
            )
        );
    }

    public function down(Schema $schema)
    {
        $schema->dropTable($this->getTablePrefix() . '_stuffs');
    }
}