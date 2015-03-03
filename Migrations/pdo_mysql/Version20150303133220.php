<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

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
            ADD locker TINYINT(1) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            DROP FOREIGN KEY FK_B797C100FAB79C10
        ");
        $this->addSql("
            DROP INDEX idx_b797c100fab79c10 ON ujm_proposal
        ");
        $this->addSql("
            CREATE INDEX IDX_2672B44BFAB79C10 ON ujm_proposal (interaction_matching_id)
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            ADD CONSTRAINT FK_B797C100FAB79C10 FOREIGN KEY (interaction_matching_id) 
            REFERENCES ujm_interaction_matching (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_category 
            DROP locker
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            DROP FOREIGN KEY FK_2672B44BFAB79C10
        ");
        $this->addSql("
            DROP INDEX idx_2672b44bfab79c10 ON ujm_proposal
        ");
        $this->addSql("
            CREATE INDEX IDX_B797C100FAB79C10 ON ujm_proposal (interaction_matching_id)
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            ADD CONSTRAINT FK_2672B44BFAB79C10 FOREIGN KEY (interaction_matching_id) 
            REFERENCES ujm_interaction_matching (id)
        ");
    }
}