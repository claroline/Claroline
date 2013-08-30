<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/30 11:28:21
 */
class Version20130830112820 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_resource_mask_decoder (
                id INTEGER NOT NULL, 
                resource_type_id INTEGER NOT NULL, 
                value INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_39D93F4298EC6B7B ON claro_resource_mask_decoder (resource_type_id)
        ");
        $this->addSql("
            CREATE TABLE claro_resource_action (
                id INTEGER NOT NULL, 
                resource_type_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                async BOOLEAN DEFAULT NULL, 
                permRequired VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_7EE4A91298EC6B7B ON claro_resource_action (resource_type_id)
        ");
        $this->addSql("
            DROP INDEX resource_rights_unique_resource_role
        ");
        $this->addSql("
            DROP INDEX IDX_3848F483D60322AC
        ");
        $this->addSql("
            DROP INDEX IDX_3848F483B87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_rights AS 
            SELECT id, 
            role_id, 
            resourceNode_id 
            FROM claro_resource_rights
        ");
        $this->addSql("
            DROP TABLE claro_resource_rights
        ");
        $this->addSql("
            CREATE TABLE claro_resource_rights (
                id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                resourceNode_id INTEGER NOT NULL, 
                mask INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_3848F483B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_3848F483D60322AC FOREIGN KEY (role_id) 
                REFERENCES claro_role (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_rights (id, role_id, resourceNode_id) 
            SELECT id, 
            role_id, 
            resourceNode_id 
            FROM __temp__claro_resource_rights
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_rights
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_rights_unique_resource_role ON claro_resource_rights (resourceNode_id, role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3848F483D60322AC ON claro_resource_rights (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3848F483B87FAB32 ON claro_resource_rights (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_resource_mask_decoder
        ");
        $this->addSql("
            DROP TABLE claro_resource_action
        ");
        $this->addSql("
            DROP INDEX IDX_3848F483D60322AC
        ");
        $this->addSql("
            DROP INDEX IDX_3848F483B87FAB32
        ");
        $this->addSql("
            DROP INDEX resource_rights_unique_resource_role
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_rights AS 
            SELECT id, 
            role_id, 
            resourceNode_id 
            FROM claro_resource_rights
        ");
        $this->addSql("
            DROP TABLE claro_resource_rights
        ");
        $this->addSql("
            CREATE TABLE claro_resource_rights (
                id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                resourceNode_id INTEGER NOT NULL, 
                can_delete BOOLEAN NOT NULL, 
                can_open BOOLEAN NOT NULL, 
                can_edit BOOLEAN NOT NULL, 
                can_copy BOOLEAN NOT NULL, 
                can_export BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_3848F483D60322AC FOREIGN KEY (role_id) 
                REFERENCES claro_role (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_3848F483B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_rights (id, role_id, resourceNode_id) 
            SELECT id, 
            role_id, 
            resourceNode_id 
            FROM __temp__claro_resource_rights
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_rights
        ");
        $this->addSql("
            CREATE INDEX IDX_3848F483D60322AC ON claro_resource_rights (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3848F483B87FAB32 ON claro_resource_rights (resourceNode_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_rights_unique_resource_role ON claro_resource_rights (resourceNode_id, role_id)
        ");
    }
}