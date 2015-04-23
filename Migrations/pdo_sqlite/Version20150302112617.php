<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/02 11:26:19
 */
class Version20150302112617 extends AbstractMigration
{
    public function up(Schema $schema)
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
            resource_id, 
            activity_parameters_id, 
            result_visible, 
            occurrence, 
            \"action\", 
            result, 
            resultMax, 
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
                resource_id INTEGER DEFAULT NULL, 
                activity_parameters_id INTEGER NOT NULL, 
                result_visible BOOLEAN DEFAULT NULL, 
                occurrence SMALLINT NOT NULL, 
                \"action\" VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
                result VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
                resultMax VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
                resultComparison SMALLINT DEFAULT NULL, 
                userType SMALLINT NOT NULL, 
                active_from DATETIME DEFAULT NULL, 
                active_until DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_6824A65E89329D25 FOREIGN KEY (resource_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6824A65E896F55DB FOREIGN KEY (activity_parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_rule (
                id, resource_id, activity_parameters_id, 
                result_visible, occurrence, \"action\", 
                result, resultMax, resultComparison, 
                userType, active_from, active_until
            ) 
            SELECT id, 
            resource_id, 
            activity_parameters_id, 
            result_visible, 
            occurrence, 
            \"action\", 
            result, 
            resultMax, 
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
            CREATE TEMPORARY TABLE __temp__claro_activity_rule AS 
            SELECT id, 
            activity_parameters_id, 
            resource_id, 
            result_visible, 
            occurrence, 
            \"action\", 
            result, 
            resultMax, 
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
                result_visible BOOLEAN DEFAULT NULL, 
                occurrence SMALLINT NOT NULL, 
                \"action\" VARCHAR(255) NOT NULL, 
                result VARCHAR(255) DEFAULT NULL, 
                resultMax VARCHAR(255) DEFAULT NULL, 
                resultComparison SMALLINT DEFAULT NULL, 
                userType SMALLINT NOT NULL, 
                active_from DATETIME DEFAULT NULL, 
                active_until DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_6824A65E896F55DB FOREIGN KEY (activity_parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6824A65E89329D25 FOREIGN KEY (resource_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6824A65EF7A2C2FC FOREIGN KEY (badge_id) 
                REFERENCES claro_badge (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_rule (
                id, activity_parameters_id, resource_id, 
                result_visible, occurrence, \"action\", 
                result, resultMax, resultComparison, 
                userType, active_from, active_until
            ) 
            SELECT id, 
            activity_parameters_id, 
            resource_id, 
            result_visible, 
            occurrence, 
            \"action\", 
            result, 
            resultMax, 
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
    }
}