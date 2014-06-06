<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/06 10:36:13
 */
class Version20140606103611 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_F75EC869A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_F75EC869896F55DB
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity_evaluation AS 
            SELECT id, 
            activity_parameters_id, 
            user_id, 
            last_date, 
            evaluation_type, 
            evaluation_status, 
            duration, 
            score, 
            score_num, 
            score_min, 
            score_max, 
            evaluation_comment, 
            details, 
            total_duration, 
            attempts_count 
            FROM claro_activity_evaluation
        ");
        $this->addSql("
            DROP TABLE claro_activity_evaluation
        ");
        $this->addSql("
            CREATE TABLE claro_activity_evaluation (
                id INTEGER NOT NULL, 
                activity_parameters_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                last_date DATETIME DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INTEGER DEFAULT NULL, 
                score VARCHAR(255) DEFAULT NULL, 
                score_num INTEGER DEFAULT NULL, 
                score_min INTEGER DEFAULT NULL, 
                score_max INTEGER DEFAULT NULL, 
                evaluation_comment VARCHAR(255) DEFAULT NULL, 
                details CLOB DEFAULT NULL, 
                total_duration INTEGER DEFAULT NULL, 
                attempts_count INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F75EC869896F55DB FOREIGN KEY (activity_parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F75EC869A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_evaluation (
                id, activity_parameters_id, user_id, 
                last_date, evaluation_type, evaluation_status, 
                duration, score, score_num, score_min, 
                score_max, evaluation_comment, details, 
                total_duration, attempts_count
            ) 
            SELECT id, 
            activity_parameters_id, 
            user_id, 
            last_date, 
            evaluation_type, 
            evaluation_status, 
            duration, 
            score, 
            score_num, 
            score_min, 
            score_max, 
            evaluation_comment, 
            details, 
            total_duration, 
            attempts_count 
            FROM __temp__claro_activity_evaluation
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity_evaluation
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869A76ED395 ON claro_activity_evaluation (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869896F55DB ON claro_activity_evaluation (activity_parameters_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_F75EC869A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_F75EC869896F55DB
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity_evaluation AS 
            SELECT id, 
            user_id, 
            activity_parameters_id, 
            last_date, 
            evaluation_type, 
            evaluation_status, 
            duration, 
            score, 
            score_num, 
            score_min, 
            score_max, 
            evaluation_comment, 
            details, 
            total_duration, 
            attempts_count 
            FROM claro_activity_evaluation
        ");
        $this->addSql("
            DROP TABLE claro_activity_evaluation
        ");
        $this->addSql("
            CREATE TABLE claro_activity_evaluation (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                activity_parameters_id INTEGER NOT NULL, 
                last_date DATETIME DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INTEGER DEFAULT NULL, 
                score VARCHAR(255) DEFAULT NULL, 
                score_num INTEGER DEFAULT NULL, 
                score_min INTEGER DEFAULT NULL, 
                score_max INTEGER DEFAULT NULL, 
                evaluation_comment VARCHAR(255) DEFAULT NULL, 
                details CLOB DEFAULT NULL, 
                total_duration INTEGER DEFAULT NULL, 
                attempts_count INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F75EC869A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F75EC869896F55DB FOREIGN KEY (activity_parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_evaluation (
                id, user_id, activity_parameters_id, 
                last_date, evaluation_type, evaluation_status, 
                duration, score, score_num, score_min, 
                score_max, evaluation_comment, details, 
                total_duration, attempts_count
            ) 
            SELECT id, 
            user_id, 
            activity_parameters_id, 
            last_date, 
            evaluation_type, 
            evaluation_status, 
            duration, 
            score, 
            score_num, 
            score_min, 
            score_max, 
            evaluation_comment, 
            details, 
            total_duration, 
            attempts_count 
            FROM __temp__claro_activity_evaluation
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity_evaluation
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869A76ED395 ON claro_activity_evaluation (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869896F55DB ON claro_activity_evaluation (activity_parameters_id)
        ");
    }
}