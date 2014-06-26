<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/18 11:32:53
 */
class Version20140618113250 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_facet 
            ADD COLUMN isVisibleByOwner SMALLINT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet 
            ADD COLUMN isVisibleByOwner SMALLINT NOT NULL 
            ADD COLUMN isEditableByOwner SMALLINT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_facet 
            DROP COLUMN isVisibleByOwner
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet 
            DROP COLUMN isVisibleByOwner 
            DROP COLUMN isEditableByOwner
        ");
    }
}