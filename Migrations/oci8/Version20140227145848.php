<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/02/27 02:58:53
 */
class Version20140227145848 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user_badge 
            ADD (
                expired_at TIMESTAMP(0) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD (
                is_expiring NUMBER(1) DEFAULT '0' NOT NULL, 
                expire_duration NUMBER(10) DEFAULT NULL, 
                expire_period NUMBER(5) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP (expired_at)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD (
                expired_at TIMESTAMP(0) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP (
                is_expiring, expire_duration, expire_period
            )
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge 
            DROP (expired_at)
        ");
    }
}