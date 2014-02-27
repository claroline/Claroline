<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/02/26 02:50:59
 */
class Version20140226145055 extends AbstractMigration
{
    public function up(Schema $schema)
    {
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
    }
}