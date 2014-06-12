<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/12 04:02:42
 */
class Version20140612160241 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_805FCB8FF7A2C2FC
        ");
        $this->addSql("
            DROP INDEX IDX_805FCB8F89329D25
        ");
        $this->addSql("
            DROP INDEX IDX_805FCB8F16F956BA
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_badge_rule AS 
            SELECT id, 
            associated_badge, 
            resource_id, 
            badge_id, 
            occurrence, 
            \"action\", 
            result, 
            resultComparison, 
            userType 
            FROM claro_badge_rule
        ");
        $this->addSql("
            DROP TABLE claro_badge_rule
        ");
        $this->addSql("
            CREATE TABLE claro_badge_rule (
                id INTEGER NOT NULL, 
                associated_badge INTEGER NOT NULL, 
                resource_id INTEGER DEFAULT NULL, 
                badge_id INTEGER DEFAULT NULL, 
                occurrence INTEGER NOT NULL, 
                \"action\" VARCHAR(255) NOT NULL, 
                result VARCHAR(255) DEFAULT NULL, 
                resultComparison INTEGER DEFAULT NULL, 
                userType INTEGER NOT NULL, 
                active_from DATETIME DEFAULT NULL, 
                active_until DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_805FCB8F16F956BA FOREIGN KEY (associated_badge) 
                REFERENCES claro_badge (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_805FCB8F89329D25 FOREIGN KEY (resource_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
                REFERENCES claro_badge (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_badge_rule (
                id, associated_badge, resource_id, 
                badge_id, occurrence, \"action\", result, 
                resultComparison, userType
            ) 
            SELECT id, 
            associated_badge, 
            resource_id, 
            badge_id, 
            occurrence, 
            \"action\", 
            result, 
            resultComparison, 
            userType 
            FROM __temp__claro_badge_rule
        ");
        $this->addSql("
            DROP TABLE __temp__claro_badge_rule
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8FF7A2C2FC ON claro_badge_rule (badge_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8F89329D25 ON claro_badge_rule (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8F16F956BA ON claro_badge_rule (associated_badge)
        ");
        $this->addSql("
            DROP INDEX IDX_F1A76182A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_F1A76182896F55DB
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity_past_evaluation AS 
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
            details 
            FROM claro_activity_past_evaluation
        ");
        $this->addSql("
            DROP TABLE claro_activity_past_evaluation
        ");
        $this->addSql("
            CREATE TABLE claro_activity_past_evaluation (
                id INTEGER NOT NULL, 
                activity_parameters_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                log_id INTEGER DEFAULT NULL, 
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
                PRIMARY KEY(id), 
                CONSTRAINT FK_F1A76182896F55DB FOREIGN KEY (activity_parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F1A76182A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F1A76182EA675D86 FOREIGN KEY (log_id) 
                REFERENCES claro_log (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_past_evaluation (
                id, activity_parameters_id, user_id, 
                last_date, evaluation_type, evaluation_status, 
                duration, score, score_num, score_min, 
                score_max, evaluation_comment, details
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
            details 
            FROM __temp__claro_activity_past_evaluation
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity_past_evaluation
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182A76ED395 ON claro_activity_past_evaluation (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182896F55DB ON claro_activity_past_evaluation (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182EA675D86 ON claro_activity_past_evaluation (log_id)
        ");
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
                log_id INTEGER DEFAULT NULL, 
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
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F75EC869EA675D86 FOREIGN KEY (log_id) 
                REFERENCES claro_log (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
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
        $this->addSql("
            CREATE INDEX IDX_F75EC869EA675D86 ON claro_activity_evaluation (log_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX user_activity_unique_evaluation ON claro_activity_evaluation (user_id, activity_parameters_id)
        ");
        $this->addSql("
            DROP INDEX IDX_6824A65E896F55DB
        ");
        $this->addSql("
            DROP INDEX IDX_6824A65E89329D25
        ");
        $this->addSql("
            DROP INDEX IDX_6824A65EF7A2C2FC
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity_rule AS 
            SELECT id, 
            resource_id, 
            activity_parameters_id, 
            badge_id, 
            occurrence, 
            \"action\", 
            result, 
            resultComparison, 
            userType 
            FROM claro_activity_rule
        ");
        $this->addSql("
            DROP TABLE claro_activity_rule
        ");
        $this->addSql("
            CREATE TABLE claro_activity_rule (
                id INTEGER NOT NULL, 
                resource_id INTEGER DEFAULT NULL, 
                activity_parameters_id INTEGER NOT NULL, 
                badge_id INTEGER DEFAULT NULL, 
                occurrence INTEGER NOT NULL, 
                \"action\" VARCHAR(255) NOT NULL, 
                result VARCHAR(255) DEFAULT NULL, 
                resultComparison INTEGER DEFAULT NULL, 
                userType INTEGER NOT NULL, 
                active_from DATETIME DEFAULT NULL, 
                active_until DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_6824A65E89329D25 FOREIGN KEY (resource_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6824A65E896F55DB FOREIGN KEY (activity_parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6824A65EF7A2C2FC FOREIGN KEY (badge_id) 
                REFERENCES claro_badge (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_rule (
                id, resource_id, activity_parameters_id, 
                badge_id, occurrence, \"action\", result, 
                resultComparison, userType
            ) 
            SELECT id, 
            resource_id, 
            activity_parameters_id, 
            badge_id, 
            occurrence, 
            \"action\", 
            result, 
            resultComparison, 
            userType 
            FROM __temp__claro_activity_rule
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity_rule
        ");
        $this->addSql("
            CREATE INDEX IDX_6824A65E896F55DB ON claro_activity_rule (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6824A65E89329D25 ON claro_activity_rule (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6824A65EF7A2C2FC ON claro_activity_rule (badge_id)
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
            DROP INDEX IDX_F75EC869EA675D86
        ");
        $this->addSql("
            DROP INDEX user_activity_unique_evaluation
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
        $this->addSql("
            DROP INDEX IDX_F1A76182A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_F1A76182896F55DB
        ");
        $this->addSql("
            DROP INDEX IDX_F1A76182EA675D86
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity_past_evaluation AS 
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
            details 
            FROM claro_activity_past_evaluation
        ");
        $this->addSql("
            DROP TABLE claro_activity_past_evaluation
        ");
        $this->addSql("
            CREATE TABLE claro_activity_past_evaluation (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                activity_parameters_id INTEGER DEFAULT NULL, 
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
                PRIMARY KEY(id), 
                CONSTRAINT FK_F1A76182A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F1A76182896F55DB FOREIGN KEY (activity_parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_past_evaluation (
                id, user_id, activity_parameters_id, 
                last_date, evaluation_type, evaluation_status, 
                duration, score, score_num, score_min, 
                score_max, evaluation_comment, details
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
            details 
            FROM __temp__claro_activity_past_evaluation
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity_past_evaluation
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182A76ED395 ON claro_activity_past_evaluation (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182896F55DB ON claro_activity_past_evaluation (activity_parameters_id)
        ");
        $this->addSql("
            DROP INDEX IDX_6824A65E896F55DB
        ");
        $this->addSql("
            DROP INDEX IDX_6824A65E89329D25
        ");
        $this->addSql("
            DROP INDEX IDX_6824A65EF7A2C2FC
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity_rule AS 
            SELECT id, 
            activity_parameters_id, 
            resource_id, 
            badge_id, 
            occurrence, 
            \"action\", 
            result, 
            resultComparison, 
            userType 
            FROM claro_activity_rule
        ");
        $this->addSql("
            DROP TABLE claro_activity_rule
        ");
        $this->addSql("
            CREATE TABLE claro_activity_rule (
                id INTEGER NOT NULL, 
                activity_parameters_id INTEGER NOT NULL, 
                resource_id INTEGER DEFAULT NULL, 
                badge_id INTEGER DEFAULT NULL, 
                occurrence INTEGER NOT NULL, 
                \"action\" VARCHAR(255) NOT NULL, 
                result VARCHAR(255) DEFAULT NULL, 
                resultComparison INTEGER DEFAULT NULL, 
                userType INTEGER NOT NULL, 
                additional_datas VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_6824A65E896F55DB FOREIGN KEY (activity_parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6824A65E89329D25 FOREIGN KEY (resource_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6824A65EF7A2C2FC FOREIGN KEY (badge_id) 
                REFERENCES claro_badge (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_rule (
                id, activity_parameters_id, resource_id, 
                badge_id, occurrence, \"action\", result, 
                resultComparison, userType
            ) 
            SELECT id, 
            activity_parameters_id, 
            resource_id, 
            badge_id, 
            occurrence, 
            \"action\", 
            result, 
            resultComparison, 
            userType 
            FROM __temp__claro_activity_rule
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity_rule
        ");
        $this->addSql("
            CREATE INDEX IDX_6824A65E896F55DB ON claro_activity_rule (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6824A65E89329D25 ON claro_activity_rule (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6824A65EF7A2C2FC ON claro_activity_rule (badge_id)
        ");
        $this->addSql("
            DROP INDEX IDX_805FCB8F16F956BA
        ");
        $this->addSql("
            DROP INDEX IDX_805FCB8F89329D25
        ");
        $this->addSql("
            DROP INDEX IDX_805FCB8FF7A2C2FC
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_badge_rule AS 
            SELECT id, 
            associated_badge, 
            resource_id, 
            badge_id, 
            occurrence, 
            \"action\", 
            result, 
            resultComparison, 
            userType 
            FROM claro_badge_rule
        ");
        $this->addSql("
            DROP TABLE claro_badge_rule
        ");
        $this->addSql("
            CREATE TABLE claro_badge_rule (
                id INTEGER NOT NULL, 
                associated_badge INTEGER NOT NULL, 
                resource_id INTEGER DEFAULT NULL, 
                badge_id INTEGER DEFAULT NULL, 
                occurrence INTEGER NOT NULL, 
                \"action\" VARCHAR(255) NOT NULL, 
                result VARCHAR(255) DEFAULT NULL, 
                resultComparison INTEGER DEFAULT NULL, 
                userType INTEGER NOT NULL, 
                additional_datas VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_805FCB8F16F956BA FOREIGN KEY (associated_badge) 
                REFERENCES claro_badge (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_805FCB8F89329D25 FOREIGN KEY (resource_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
                REFERENCES claro_badge (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_badge_rule (
                id, associated_badge, resource_id, 
                badge_id, occurrence, \"action\", result, 
                resultComparison, userType
            ) 
            SELECT id, 
            associated_badge, 
            resource_id, 
            badge_id, 
            occurrence, 
            \"action\", 
            result, 
            resultComparison, 
            userType 
            FROM __temp__claro_badge_rule
        ");
        $this->addSql("
            DROP TABLE __temp__claro_badge_rule
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8F16F956BA ON claro_badge_rule (associated_badge)
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8F89329D25 ON claro_badge_rule (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8FF7A2C2FC ON claro_badge_rule (badge_id)
        ");
    }
}