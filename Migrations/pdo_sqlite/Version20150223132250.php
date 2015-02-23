<?php

namespace UJM\ExoBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/23 01:22:52
 */
class Version20150223132250 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_label 
            ADD COLUMN position_force BOOLEAN DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_label 
            ADD COLUMN ordre INTEGER NOT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_matching 
            ADD COLUMN shuffle BOOLEAN DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            ADD COLUMN position_force BOOLEAN DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE ujm_proposal 
            ADD COLUMN ordre INTEGER NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_AC9801C7886DEE8F
        ");
        $this->addSql("
            DROP INDEX IDX_AC9801C7F881A129
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__ujm_interaction_matching AS 
            SELECT id, 
            interaction_id, 
            type_matching_id 
            FROM ujm_interaction_matching
        ");
        $this->addSql("
            DROP TABLE ujm_interaction_matching
        ");
        $this->addSql("
            CREATE TABLE ujm_interaction_matching (
                id INTEGER NOT NULL, 
                interaction_id INTEGER DEFAULT NULL, 
                type_matching_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_AC9801C7886DEE8F FOREIGN KEY (interaction_id) 
                REFERENCES ujm_interaction (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_AC9801C7F881A129 FOREIGN KEY (type_matching_id) 
                REFERENCES ujm_type_matching (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO ujm_interaction_matching (
                id, interaction_id, type_matching_id
            ) 
            SELECT id, 
            interaction_id, 
            type_matching_id 
            FROM __temp__ujm_interaction_matching
        ");
        $this->addSql("
            DROP TABLE __temp__ujm_interaction_matching
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_AC9801C7886DEE8F ON ujm_interaction_matching (interaction_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_AC9801C7F881A129 ON ujm_interaction_matching (type_matching_id)
        ");
        $this->addSql("
            DROP INDEX IDX_C22A1EB5FAB79C10
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__ujm_label AS 
            SELECT id, 
            interaction_matching_id, 
            value, 
            score_right_response 
            FROM ujm_label
        ");
        $this->addSql("
            DROP TABLE ujm_label
        ");
        $this->addSql("
            CREATE TABLE ujm_label (
                id INTEGER NOT NULL, 
                interaction_matching_id INTEGER DEFAULT NULL, 
                value CLOB NOT NULL, 
                score_right_response DOUBLE PRECISION DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_C22A1EB5FAB79C10 FOREIGN KEY (interaction_matching_id) 
                REFERENCES ujm_interaction_matching (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO ujm_label (
                id, interaction_matching_id, value, 
                score_right_response
            ) 
            SELECT id, 
            interaction_matching_id, 
            value, 
            score_right_response 
            FROM __temp__ujm_label
        ");
        $this->addSql("
            DROP TABLE __temp__ujm_label
        ");
        $this->addSql("
            CREATE INDEX IDX_C22A1EB5FAB79C10 ON ujm_label (interaction_matching_id)
        ");
        $this->addSql("
            DROP INDEX IDX_2672B44BFAB79C10
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__ujm_proposal AS 
            SELECT id, 
            interaction_matching_id, 
            value 
            FROM ujm_proposal
        ");
        $this->addSql("
            DROP TABLE ujm_proposal
        ");
        $this->addSql("
            CREATE TABLE ujm_proposal (
                id INTEGER NOT NULL, 
                interaction_matching_id INTEGER DEFAULT NULL, 
                value CLOB NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_2672B44BFAB79C10 FOREIGN KEY (interaction_matching_id) 
                REFERENCES ujm_interaction_matching (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO ujm_proposal (
                id, interaction_matching_id, value
            ) 
            SELECT id, 
            interaction_matching_id, 
            value 
            FROM __temp__ujm_proposal
        ");
        $this->addSql("
            DROP TABLE __temp__ujm_proposal
        ");
        $this->addSql("
            CREATE INDEX IDX_2672B44BFAB79C10 ON ujm_proposal (interaction_matching_id)
        ");
    }
}