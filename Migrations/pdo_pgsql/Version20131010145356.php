<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/10 02:53:57
 */
class Version20131010145356 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            ADD picture VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD description TEXT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP picture
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP description
        ");
    }
}