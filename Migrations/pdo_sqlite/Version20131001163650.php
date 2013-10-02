<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/01 04:36:51
 */
class Version20131001163650 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_21EA11F73B21E9C
        ");
        $this->addSql("
            DROP INDEX IDX_21EA11FB87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_step2resourceNode AS
            SELECT id,
            step_id,
            resourceOrder,
            resourceNode_id,
            propagated,
            excluded
            FROM innova_step2resourceNode
        ");
        $this->addSql("
            DROP TABLE innova_step2resourceNode
        ");
        $this->addSql("
            CREATE TABLE innova_step2resourceNode (
                id INTEGER NOT NULL,
                step_id INTEGER DEFAULT NULL,
                resourceOrder INTEGER DEFAULT NULL,
                resourceNode_id INTEGER DEFAULT NULL,
                propagated BOOLEAN NOT NULL,
                excluded BOOLEAN NOT NULL,
                PRIMARY KEY(id),
                CONSTRAINT FK_21EA11F73B21E9C FOREIGN KEY (step_id)
                REFERENCES innova_step (id) NOT DEFERRABLE INITIALLY IMMEDIATE,
                CONSTRAINT FK_21EA11FB87FAB32 FOREIGN KEY (resourceNode_id)
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_step2resourceNode (
                id, step_id, resourceOrder, resourceNode_id,
                propagated, excluded
            )
            SELECT id,
            step_id,
            resourceOrder,
            resourceNode_id,
            propagated,
            excluded
            FROM __temp__innova_step2resourceNode
        ");
        $this->addSql("
            DROP TABLE __temp__innova_step2resourceNode
        ");
        $this->addSql("
            CREATE INDEX IDX_21EA11F73B21E9C ON innova_step2resourceNode (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_21EA11FB87FAB32 ON innova_step2resourceNode (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_21EA11F73B21E9C
        ");
        $this->addSql("
            DROP INDEX IDX_21EA11FB87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_step2resourceNode AS
            SELECT id,
            step_id,
            propagated,
            excluded,
            resourceOrder,
            resourceNode_id
            FROM innova_step2resourceNode
        ");
        $this->addSql("
            DROP TABLE innova_step2resourceNode
        ");
        $this->addSql("
            CREATE TABLE innova_step2resourceNode (
                id INTEGER NOT NULL,
                step_id INTEGER DEFAULT NULL,
                propagated BOOLEAN NOT NULL,
                excluded BOOLEAN NOT NULL,
                resourceOrder INTEGER NOT NULL,
                resourceNode_id INTEGER DEFAULT NULL,
                PRIMARY KEY(id),
                CONSTRAINT FK_21EA11F73B21E9C FOREIGN KEY (step_id)
                REFERENCES innova_step (id) NOT DEFERRABLE INITIALLY IMMEDIATE,
                CONSTRAINT FK_21EA11FB87FAB32 FOREIGN KEY (resourceNode_id)
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO innova_step2resourceNode (
                id, step_id, propagated, excluded,
                resourceOrder, resourceNode_id
            )
            SELECT id,
            step_id,
            propagated,
            excluded,
            resourceOrder,
            resourceNode_id
            FROM __temp__innova_step2resourceNode
        ");
        $this->addSql("
            DROP TABLE __temp__innova_step2resourceNode
        ");
        $this->addSql("
            CREATE INDEX IDX_21EA11F73B21E9C ON innova_step2resourceNode (step_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_21EA11FB87FAB32 ON innova_step2resourceNode (resourceNode_id)
        ");
    }
}
