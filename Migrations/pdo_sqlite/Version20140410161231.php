<?php

namespace UJM\ExoBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/10 04:12:33
 */
class Version20140410161231 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_exercise 
            ADD COLUMN published BOOLEAN NOT NULL
        ");
        $this->addSql("
            UPDATE ujm_exercise SET published=1
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_374DF525B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__ujm_exercise AS 
            SELECT id, 
            title, 
            description, 
            shuffle, 
            nb_question, 
            keepSameQuestion, 
            date_create, 
            duration, 
            nb_question_page, 
            doprint, 
            max_attempts, 
            correction_mode, 
            date_correction, 
            mark_mode, 
            start_date, 
            use_date_end, 
            end_date, 
            disp_button_interrupt, 
            lock_attempt, 
            resourceNode_id 
            FROM ujm_exercise
        ");
        $this->addSql("
            DROP TABLE ujm_exercise
        ");
        $this->addSql("
            CREATE TABLE ujm_exercise (
                id INTEGER NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                shuffle BOOLEAN DEFAULT NULL, 
                nb_question INTEGER NOT NULL, 
                keepSameQuestion BOOLEAN DEFAULT NULL, 
                date_create DATETIME NOT NULL, 
                duration INTEGER NOT NULL, 
                nb_question_page INTEGER NOT NULL, 
                doprint BOOLEAN DEFAULT NULL, 
                max_attempts INTEGER NOT NULL, 
                correction_mode VARCHAR(255) NOT NULL, 
                date_correction DATETIME DEFAULT NULL, 
                mark_mode VARCHAR(255) NOT NULL, 
                start_date DATETIME NOT NULL, 
                use_date_end BOOLEAN DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                disp_button_interrupt BOOLEAN DEFAULT NULL, 
                lock_attempt BOOLEAN DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_374DF525B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO ujm_exercise (
                id, title, description, shuffle, nb_question, 
                keepSameQuestion, date_create, duration, 
                nb_question_page, doprint, max_attempts, 
                correction_mode, date_correction, 
                mark_mode, start_date, use_date_end, 
                end_date, disp_button_interrupt, 
                lock_attempt, resourceNode_id
            ) 
            SELECT id, 
            title, 
            description, 
            shuffle, 
            nb_question, 
            keepSameQuestion, 
            date_create, 
            duration, 
            nb_question_page, 
            doprint, 
            max_attempts, 
            correction_mode, 
            date_correction, 
            mark_mode, 
            start_date, 
            use_date_end, 
            end_date, 
            disp_button_interrupt, 
            lock_attempt, 
            resourceNode_id 
            FROM __temp__ujm_exercise
        ");
        $this->addSql("
            DROP TABLE __temp__ujm_exercise
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_374DF525B87FAB32 ON ujm_exercise (resourceNode_id)
        ");
    }
}