<?php

namespace UJM\ExoBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/03 01:32:22
 */
class Version20150303133220 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_category 
            ADD locker BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER INDEX idx_b797c100fab79c10 RENAME TO IDX_2672B44BFAB79C10
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_category 
            DROP locker
        ");
        $this->addSql("
            ALTER INDEX idx_2672b44bfab79c10 RENAME TO IDX_B797C100FAB79C10
        ");
    }
}