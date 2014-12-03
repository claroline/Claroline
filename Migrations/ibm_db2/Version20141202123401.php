<?php

namespace UJM\ExoBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/12/02 12:34:04
 */
class Version20141202123401 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE ujm_proposal_label (
                proposal_id INTEGER NOT NULL, 
                label_id INTEGER NOT NULL, 
                PRIMARY KEY(proposal_id, label_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F9B1BA4AF4792058 ON ujm_proposal_label (proposal_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F9B1BA4A33B92F39 ON ujm_proposal_label (label_id)
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal_label 
            ADD CONSTRAINT FK_F9B1BA4AF4792058 FOREIGN KEY (proposal_id) 
            REFERENCES ujm_proposal (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal_label 
            ADD CONSTRAINT FK_F9B1BA4A33B92F39 FOREIGN KEY (label_id) 
            REFERENCES ujm_label (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            DROP COLUMN label_id
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            DROP FOREIGN KEY FK_2672B44B33B92F39
        ");
        $this->addSql("
            DROP INDEX IDX_2672B44B33B92F39
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE ujm_proposal_label
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            ADD COLUMN label_id INTEGER DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            ADD CONSTRAINT FK_2672B44B33B92F39 FOREIGN KEY (label_id) 
            REFERENCES ujm_label (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2672B44B33B92F39 ON ujm_proposal (label_id)
        ");
    }
}