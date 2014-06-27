<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/05 02:22:51
 */
class Version20140605142250 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_activity_rule_action (
                id INTEGER NOT NULL, 
                resource_type_id INTEGER DEFAULT NULL, 
                log_action VARCHAR(255) NOT NULL, 
                rule_type VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C8835D2098EC6B7B ON claro_activity_rule_action (resource_type_id)
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
                PRIMARY KEY(id)
            )
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
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869A76ED395 ON claro_activity_evaluation (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869896F55DB ON claro_activity_evaluation (activity_parameters_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity_parameters (
                id INTEGER NOT NULL, 
                activity_id INTEGER DEFAULT NULL, 
                max_duration INTEGER DEFAULT NULL, 
                max_attempts INTEGER DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E2EE25E281C06096 ON claro_activity_parameters (activity_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity_secondary_resources (
                activity_parameters_id INTEGER NOT NULL, 
                resource_node_id INTEGER NOT NULL, 
                PRIMARY KEY(
                    activity_parameters_id, resource_node_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_713242A7896F55DB ON claro_activity_secondary_resources (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_713242A71BAD783F ON claro_activity_secondary_resources (resource_node_id)
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
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182A76ED395 ON claro_activity_past_evaluation (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182896F55DB ON claro_activity_past_evaluation (activity_parameters_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CACB87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity AS 
            SELECT id, 
            instruction, 
            resourceNode_id 
            FROM claro_activity
        ");
        $this->addSql("
            DROP TABLE claro_activity
        ");
        $this->addSql("
            CREATE TABLE claro_activity (
                id INTEGER NOT NULL, 
                parameters_id INTEGER DEFAULT NULL, 
                resourceNode_id INTEGER NOT NULL, 
                description VARCHAR(255) NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_E4A67CAC88BD9C1F FOREIGN KEY (parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity (id, description, resourceNode_id) 
            SELECT id, 
            instruction, 
            resourceNode_id 
            FROM __temp__claro_activity
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CACB87FAB32 ON claro_activity (resourceNode_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CAC88BD9C1F ON claro_activity (parameters_id)
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD COLUMN additional_datas VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_activity_rule_action
        ");
        $this->addSql("
            DROP TABLE claro_activity_rule
        ");
        $this->addSql("
            DROP TABLE claro_activity_evaluation
        ");
        $this->addSql("
            DROP TABLE claro_activity_parameters
        ");
        $this->addSql("
            DROP TABLE claro_activity_secondary_resources
        ");
        $this->addSql("
            DROP TABLE claro_activity_past_evaluation
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CACB87FAB32
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CAC88BD9C1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity AS 
            SELECT id, 
            description, 
            resourceNode_id 
            FROM claro_activity
        ");
        $this->addSql("
            DROP TABLE claro_activity
        ");
        $this->addSql("
            CREATE TABLE claro_activity (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                instruction VARCHAR(255) NOT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity (id, instruction, resourceNode_id) 
            SELECT id, 
            description, 
            resourceNode_id 
            FROM __temp__claro_activity
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CACB87FAB32 ON claro_activity (resourceNode_id)
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