<?php

namespace UJM\ExoBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/02/13 01:49:38
 */
class Version20140213134937 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_interaction_hole 
            ADD COLUMN htmlWithoutValue CLOB DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_7343FAC1886DEE8F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__ujm_interaction_hole AS 
            SELECT id, 
            interaction_id, 
            html 
            FROM ujm_interaction_hole
        ");
        $this->addSql("
            DROP TABLE ujm_interaction_hole
        ");
        $this->addSql("
            CREATE TABLE ujm_interaction_hole (
                id INTEGER NOT NULL, 
                interaction_id INTEGER DEFAULT NULL, 
                html CLOB NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_7343FAC1886DEE8F FOREIGN KEY (interaction_id) 
                REFERENCES ujm_interaction (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO ujm_interaction_hole (id, interaction_id, html) 
            SELECT id, 
            interaction_id, 
            html 
            FROM __temp__ujm_interaction_hole
        ");
        $this->addSql("
            DROP TABLE __temp__ujm_interaction_hole
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_7343FAC1886DEE8F ON ujm_interaction_hole (interaction_id)
        ");
    }
}