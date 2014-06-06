<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/26 11:12:03
 */
class Version20140526111202 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            ADD expiration_date DATETIME2(6) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_role 
            ADD maxUsers INT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_role 
            DROP COLUMN maxUsers
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN expiration_date
        ");
    }
}