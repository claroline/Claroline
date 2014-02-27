<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/02/27 02:58:54
 */
class Version20140227145848 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user_badge 
            ADD expired_at DATETIME DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD is_expiring TINYINT(1) DEFAULT '0' NOT NULL, 
            ADD expire_duration INT DEFAULT NULL, 
            ADD expire_period SMALLINT DEFAULT NULL, 
            DROP expired_at
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD expired_at DATETIME DEFAULT NULL, 
            DROP is_expiring, 
            DROP expire_duration, 
            DROP expire_period
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge 
            DROP expired_at
        ");
    }
}