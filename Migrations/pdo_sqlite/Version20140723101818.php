<?php

namespace UJM\ExoBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/23 10:18:20
 */
class Version20140723101818 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE ujm_proposal (
                id INTEGER NOT NULL, 
                interaction_matching_id INTEGER DEFAULT NULL, 
                value CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B797C100FAB79C10 ON ujm_proposal (interaction_matching_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_interaction_matching (
                id INTEGER NOT NULL, 
                interaction_id INTEGER DEFAULT NULL, 
                type_matching_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_AC9801C7886DEE8F ON ujm_interaction_matching (interaction_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_AC9801C7F881A129 ON ujm_interaction_matching (type_matching_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_label (
                id INTEGER NOT NULL, 
                interaction_matching_id INTEGER DEFAULT NULL, 
                value CLOB NOT NULL, 
                score_right_response DOUBLE PRECISION DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C22A1EB5FAB79C10 ON ujm_label (interaction_matching_id)
        ");
        $this->addSql("
            CREATE TABLE ujm_type_matching (
                id INTEGER NOT NULL, 
                value VARCHAR(255) NOT NULL, 
                code INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_45333F9A77153098 ON ujm_type_matching (code)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE ujm_proposal
        ");
        $this->addSql("
            DROP TABLE ujm_interaction_matching
        ");
        $this->addSql("
            DROP TABLE ujm_label
        ");
        $this->addSql("
            DROP TABLE ujm_type_matching
        ");
    }
}