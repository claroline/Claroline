<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/01 04:45:56
 */
class Version20140701164555 extends AbstractMigration
{
    public function up(Schema $schema)
    {
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
            CREATE UNIQUE INDEX activity_rule_unique_action_resource_type ON claro_activity_rule_action (log_action, resource_type_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_C8835D2098EC6B7B
        ");
        $this->addSql("
            DROP INDEX activity_rule_unique_action_resource_type
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
    }
}