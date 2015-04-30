<?php

namespace UJM\ExoBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/16 10:45:38
 */
class Version20150416104535 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_word_response 
            ADD COLUMN caseSensitive BOOLEAN DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_4E1930C598DDBDFD
        ");
        $this->addSql("
            DROP INDEX IDX_4E1930C515ADE12C
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__ujm_word_response AS 
            SELECT id, 
            interaction_open_id, 
            hole_id, 
            response, 
            score 
            FROM ujm_word_response
        ");
        $this->addSql("
            DROP TABLE ujm_word_response
        ");
        $this->addSql("
            CREATE TABLE ujm_word_response (
                id INTEGER NOT NULL, 
                interaction_open_id INTEGER DEFAULT NULL, 
                hole_id INTEGER DEFAULT NULL, 
                response VARCHAR(255) NOT NULL, 
                score DOUBLE PRECISION NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_4E1930C598DDBDFD FOREIGN KEY (interaction_open_id) 
                REFERENCES ujm_interaction_open (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_4E1930C515ADE12C FOREIGN KEY (hole_id) 
                REFERENCES ujm_hole (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO ujm_word_response (
                id, interaction_open_id, hole_id, 
                response, score
            ) 
            SELECT id, 
            interaction_open_id, 
            hole_id, 
            response, 
            score 
            FROM __temp__ujm_word_response
        ");
        $this->addSql("
            DROP TABLE __temp__ujm_word_response
        ");
        $this->addSql("
            CREATE INDEX IDX_4E1930C598DDBDFD ON ujm_word_response (interaction_open_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_4E1930C515ADE12C ON ujm_word_response (hole_id)
        ");
    }
}