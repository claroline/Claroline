<?php

namespace Claroline\DocumentBundle\Migrations;

use Claroline\CoreBundle\Library\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120303000000 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createDocumentTable($schema);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('claro_document');
    }

    private function createDocumentTable(Schema $schema)
    {
        $table = $schema->createTable('claro_document');

        $this->addId($table);
        $table->addColumn('name', 'string', array('lenght' => 255));
        $table->addColumn('path', 'string', array('lenght' => 255));
        $table->addColumn('date_upload', 'datetime');
        $table->addColumn('size', 'integer', array('notnull' => true));
    }

}
