<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

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
            ADD COLUMN expired_at TIMESTAMP(0) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD COLUMN is_expiring SMALLINT NOT NULL 
            ADD COLUMN expire_duration INTEGER DEFAULT NULL 
            ADD COLUMN expire_period SMALLINT DEFAULT NULL 
            DROP COLUMN expired_at
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD COLUMN expired_at TIMESTAMP(0) DEFAULT NULL 
            DROP COLUMN is_expiring 
            DROP COLUMN expire_duration 
            DROP COLUMN expire_period
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge 
            DROP COLUMN expired_at
        ");
    }
}