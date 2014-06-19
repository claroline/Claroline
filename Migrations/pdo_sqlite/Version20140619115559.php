<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/19 11:56:00
 */
class Version20140619115559 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD COLUMN resultMax VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            DROP INDEX IDX_C8835D2098EC6B7B
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity_rule_action AS 
            SELECT id, 
            resource_type_id, 
            log_action 
            FROM claro_activity_rule_action
        ");
        $this->addSql("
            DROP TABLE claro_activity_rule_action
        ");
        $this->addSql("
            CREATE TABLE claro_activity_rule_action (
                id INTEGER NOT NULL, 
                resource_type_id INTEGER DEFAULT NULL, 
                log_action VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_C8835D2098EC6B7B FOREIGN KEY (resource_type_id) 
                REFERENCES claro_resource_type (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_rule_action (id, resource_type_id, log_action) 
            SELECT id, 
            resource_type_id, 
            log_action 
            FROM __temp__claro_activity_rule_action
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity_rule_action
        ");
        $this->addSql("
            CREATE INDEX IDX_C8835D2098EC6B7B ON claro_activity_rule_action (resource_type_id)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD COLUMN resultMax VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
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
            userType, 
            active_from, 
            active_until 
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
                active_from DATETIME DEFAULT NULL, 
                active_until DATETIME DEFAULT NULL, 
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
                resultComparison, userType, active_from, 
                active_until
            ) 
            SELECT id, 
            activity_parameters_id, 
            resource_id, 
            badge_id, 
            occurrence, 
            \"action\", 
            result, 
            resultComparison, 
            userType, 
            active_from, 
            active_until 
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
            ALTER TABLE claro_activity_rule_action 
            ADD COLUMN rule_type VARCHAR(255) DEFAULT NULL
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
            userType, 
            active_from, 
            active_until 
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
                resultComparison, userType, active_from, 
                active_until
            ) 
            SELECT id, 
            associated_badge, 
            resource_id, 
            badge_id, 
            occurrence, 
            \"action\", 
            result, 
            resultComparison, 
            userType, 
            active_from, 
            active_until 
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