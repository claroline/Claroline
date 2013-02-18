<?php

namespace Claroline\RssReaderBundle\Migrations;

use Claroline\CoreBundle\Library\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20121002000000 extends BundleMigration
{

    public function up(Schema $schema)
    {
        $this->createConfigTable($schema);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('claro_rssreader_configuration');
    }

    public function createConfigTable(Schema $schema)
    {
        $table = $schema->createTable('claro_rssreader_configuration');
        $this->addId($table);
        $table->addColumn('workspace_id', 'integer', array('notnull' => false));
        $table->addColumn('url', 'string', array('length' => 255));
        $table->addColumn('is_desktop', 'boolean');
        $table->addColumn('is_default', 'boolean');
        $table->addColumn('user_id', 'integer', array('notnull' => false));

        $table->addForeignKeyConstraint(
            $schema->getTable('claro_workspace'), array('workspace_id'), array('id'), array('onDelete' => 'CASCADE')
        );
    }
}

