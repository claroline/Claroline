<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/02/26 02:50:58
 */
class Version20140226145055 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD is_expiring BOOLEAN DEFAULT 'false' NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD expire_duration INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD expire_period SMALLINT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP expired_at
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD expired_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP is_expiring
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP expire_duration
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP expire_period
        ");
    }
}