<?php

namespace UJM\ExoBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/27 01:06:34
 */
class Version20140127130633 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_E9F4F52575EBD64D
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__ujm_hole AS 
            SELECT id, 
            interaction_hole_id, 
            size, 
            position, 
            orthography, 
            selector 
            FROM ujm_hole
        ");
        $this->addSql("
            DROP TABLE ujm_hole
        ");
        $this->addSql("
            CREATE TABLE ujm_hole (
                id INTEGER NOT NULL, 
                interaction_hole_id INTEGER DEFAULT NULL, 
                size INTEGER NOT NULL, 
                position INTEGER DEFAULT NULL, 
                orthography BOOLEAN DEFAULT NULL, 
                selector BOOLEAN DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_E9F4F52575EBD64D FOREIGN KEY (interaction_hole_id) 
                REFERENCES ujm_interaction_hole (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO ujm_hole (
                id, interaction_hole_id, size, position, 
                orthography, selector
            ) 
            SELECT id, 
            interaction_hole_id, 
            size, 
            position, 
            orthography, 
            selector 
            FROM __temp__ujm_hole
        ");
        $this->addSql("
            DROP TABLE __temp__ujm_hole
        ");
        $this->addSql("
            CREATE INDEX IDX_E9F4F52575EBD64D ON ujm_hole (interaction_hole_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_hole 
            ADD COLUMN score DOUBLE PRECISION NOT NULL
        ");
    }
}