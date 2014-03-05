<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/05 09:24:30
 */
class Version20140305092429 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_tools 
            ADD COLUMN is_locked_for_admin SMALLINT NOT NULL 
            ADD COLUMN is_anonymous_excluded SMALLINT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_tools 
            DROP COLUMN is_locked_for_admin 
            DROP COLUMN is_anonymous_excluded
        ");
    }
}