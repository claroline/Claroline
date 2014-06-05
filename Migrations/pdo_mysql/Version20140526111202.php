<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/26 11:12:02
 */
class Version20140526111202 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            ADD expiration_date DATETIME NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_role 
            ADD maxUsers INT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_role 
            DROP maxUsers
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP expiration_date
        ");
    }
}