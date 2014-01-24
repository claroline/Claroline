<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/08 02:44:02
 */
class Version20140108144400 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_805FCB8F89329D25
        ");
        $this->addSql("
            DROP INDEX IDX_805FCB8FF7A2C2FC
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_badge_rule AS 
            SELECT id, 
            badge_id, 
            resource_id, 
            occurrence, 
            \"action\", 
            result, 
            resultComparison 
            FROM claro_badge_rule
        ");
        $this->addSql("
            DROP TABLE claro_badge_rule
        ");
        $this->addSql("
            CREATE TABLE claro_badge_rule (
                id INTEGER NOT NULL, 
                badge_id INTEGER DEFAULT NULL, 
                resource_id INTEGER DEFAULT NULL, 
                associated_badge INTEGER NOT NULL, 
                occurrence INTEGER NOT NULL, 
                \"action\" VARCHAR(255) NOT NULL, 
                result VARCHAR(255) DEFAULT NULL, 
                resultComparison INTEGER DEFAULT NULL, 
                userType INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
                REFERENCES claro_badge (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_805FCB8F89329D25 FOREIGN KEY (resource_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_805FCB8F16F956BA FOREIGN KEY (associated_badge) 
                REFERENCES claro_badge (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_badge_rule (
                id, badge_id, resource_id, occurrence, 
                \"action\", result, resultComparison
            ) 
            SELECT id, 
            badge_id, 
            resource_id, 
            occurrence, 
            \"action\", 
            result, 
            resultComparison 
            FROM __temp__claro_badge_rule
        ");
        $this->addSql("
            DROP TABLE __temp__claro_badge_rule
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8F89329D25 ON claro_badge_rule (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8FF7A2C2FC ON claro_badge_rule (badge_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8F16F956BA ON claro_badge_rule (associated_badge)
        ");
    }

    public function down(Schema $schema)
    {
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
            resource_id, 
            badge_id, 
            occurrence, 
            \"action\", 
            result, 
            resultComparison 
            FROM claro_badge_rule
        ");
        $this->addSql("
            DROP TABLE claro_badge_rule
        ");
        $this->addSql("
            CREATE TABLE claro_badge_rule (
                id INTEGER NOT NULL, 
                resource_id INTEGER DEFAULT NULL, 
                badge_id INTEGER NOT NULL, 
                occurrence INTEGER NOT NULL, 
                \"action\" VARCHAR(255) NOT NULL, 
                result VARCHAR(255) DEFAULT NULL, 
                resultComparison INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_805FCB8F89329D25 FOREIGN KEY (resource_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
                REFERENCES claro_badge (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_badge_rule (
                id, resource_id, badge_id, occurrence, 
                \"action\", result, resultComparison
            ) 
            SELECT id, 
            resource_id, 
            badge_id, 
            occurrence, 
            \"action\", 
            result, 
            resultComparison 
            FROM __temp__claro_badge_rule
        ");
        $this->addSql("
            DROP TABLE __temp__claro_badge_rule
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8F89329D25 ON claro_badge_rule (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8FF7A2C2FC ON claro_badge_rule (badge_id)
        ");
    }
}