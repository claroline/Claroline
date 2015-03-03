<?php

namespace UJM\ExoBundle\Migrations\pdo_sqlsrv;

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
            ADD locker BIT NOT NULL
        ");
        $this->addSql("
            EXEC sp_RENAME N 'ujm_proposal.idx_b797c100fab79c10', 
            N 'IDX_2672B44BFAB79C10', 
            N 'INDEX'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_category 
            DROP COLUMN locker
        ");
        $this->addSql("
            EXEC sp_RENAME N 'ujm_proposal.idx_2672b44bfab79c10', 
            N 'IDX_B797C100FAB79C10', 
            N 'INDEX'
        ");
    }
}