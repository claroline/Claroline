<?php

namespace Innova\CollecticielBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/07 07:46:37
 */
class Version20140407074633 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_drop 
            ADD COLUMN auto_closed_drop SMALLINT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone ALTER auto_close_opened_drops_when_time_is_up auto_close_opened_drops_when_time_is_up SMALLINT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_drop 
            DROP COLUMN auto_closed_drop
        ");
        $this->addSql("
            ALTER TABLE innova_collecticielbundle_dropzone ALTER auto_close_opened_drops_when_time_is_up auto_close_opened_drops_when_time_is_up SMALLINT NOT NULL
        ");
    }
}