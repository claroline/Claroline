<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/03 09:12:14
 */
class Version20130903091212 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_97FAB91F12D3860F
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FCD53EDB6
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FC6F122B2
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F7E3C61F9
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F98EC6B7B
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FD60322AC
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FB87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_log AS 
            SELECT id, 
            doer_id, 
            owner_id, 
            workspace_id, 
            resource_type_id, 
            receiver_group_id, 
            receiver_id, 
            role_id, 
            resourceNode_id, 
            \"action\", 
            date_log, 
            short_date_log, 
            details, 
            doer_type, 
            doer_ip, 
            tool_name 
            FROM claro_log
        ");
        $this->addSql("
            DROP TABLE claro_log
        ");
        $this->addSql("
            CREATE TABLE claro_log (
                id INTEGER NOT NULL, 
                doer_id INTEGER DEFAULT NULL, 
                owner_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                resource_type_id INTEGER DEFAULT NULL, 
                receiver_group_id INTEGER DEFAULT NULL, 
                receiver_id INTEGER DEFAULT NULL, 
                role_id INTEGER DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                \"action\" VARCHAR(255) NOT NULL, 
                date_log DATETIME NOT NULL, 
                short_date_log DATE NOT NULL, 
                details CLOB DEFAULT NULL, 
                doer_type VARCHAR(255) NOT NULL, 
                doer_ip VARCHAR(255) DEFAULT NULL, 
                tool_name VARCHAR(255) DEFAULT NULL, 
                is_displayed_in_admin BOOLEAN NOT NULL, 
                is_displayed_in_workspace BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_97FAB91F12D3860F FOREIGN KEY (doer_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91F7E3C61F9 FOREIGN KEY (owner_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91F82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91F98EC6B7B FOREIGN KEY (resource_type_id) 
                REFERENCES claro_resource_type (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91FB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91FC6F122B2 FOREIGN KEY (receiver_group_id) 
                REFERENCES claro_group (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91FCD53EDB6 FOREIGN KEY (receiver_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91FD60322AC FOREIGN KEY (role_id) 
                REFERENCES claro_role (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_log (
                id, doer_id, owner_id, workspace_id, 
                resource_type_id, receiver_group_id, 
                receiver_id, role_id, resourceNode_id, 
                \"action\", date_log, short_date_log, 
                details, doer_type, doer_ip, tool_name
            ) 
            SELECT id, 
            doer_id, 
            owner_id, 
            workspace_id, 
            resource_type_id, 
            receiver_group_id, 
            receiver_id, 
            role_id, 
            resourceNode_id, 
            \"action\", 
            date_log, 
            short_date_log, 
            details, 
            doer_type, 
            doer_ip, 
            tool_name 
            FROM __temp__claro_log
        ");
        $this->addSql("
            DROP TABLE __temp__claro_log
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F12D3860F ON claro_log (doer_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FCD53EDB6 ON claro_log (receiver_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FC6F122B2 ON claro_log (receiver_group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F7E3C61F9 ON claro_log (owner_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F82D40A1F ON claro_log (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F98EC6B7B ON claro_log (resource_type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FD60322AC ON claro_log (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FB87FAB32 ON claro_log (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_97FAB91F12D3860F
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FCD53EDB6
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FC6F122B2
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F7E3C61F9
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FB87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91F98EC6B7B
        ");
        $this->addSql("
            DROP INDEX IDX_97FAB91FD60322AC
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_log AS 
            SELECT id, 
            doer_id, 
            receiver_id, 
            receiver_group_id, 
            owner_id, 
            workspace_id, 
            resource_type_id, 
            role_id, 
            \"action\", 
            date_log, 
            short_date_log, 
            details, 
            doer_type, 
            doer_ip, 
            tool_name, 
            resourceNode_id 
            FROM claro_log
        ");
        $this->addSql("
            DROP TABLE claro_log
        ");
        $this->addSql("
            CREATE TABLE claro_log (
                id INTEGER NOT NULL, 
                doer_id INTEGER DEFAULT NULL, 
                receiver_id INTEGER DEFAULT NULL, 
                receiver_group_id INTEGER DEFAULT NULL, 
                owner_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                resource_type_id INTEGER DEFAULT NULL, 
                role_id INTEGER DEFAULT NULL, 
                \"action\" VARCHAR(255) NOT NULL, 
                date_log DATETIME NOT NULL, 
                short_date_log DATE NOT NULL, 
                details CLOB DEFAULT NULL, 
                doer_type VARCHAR(255) NOT NULL, 
                doer_ip VARCHAR(255) DEFAULT NULL, 
                tool_name VARCHAR(255) DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                child_type VARCHAR(255) DEFAULT NULL, 
                child_action VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_97FAB91F12D3860F FOREIGN KEY (doer_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91FCD53EDB6 FOREIGN KEY (receiver_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91FC6F122B2 FOREIGN KEY (receiver_group_id) 
                REFERENCES claro_group (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91F7E3C61F9 FOREIGN KEY (owner_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91F82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91FB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91F98EC6B7B FOREIGN KEY (resource_type_id) 
                REFERENCES claro_resource_type (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_97FAB91FD60322AC FOREIGN KEY (role_id) 
                REFERENCES claro_role (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_log (
                id, doer_id, receiver_id, receiver_group_id, 
                owner_id, workspace_id, resource_type_id, 
                role_id, \"action\", date_log, short_date_log, 
                details, doer_type, doer_ip, tool_name, 
                resourceNode_id
            ) 
            SELECT id, 
            doer_id, 
            receiver_id, 
            receiver_group_id, 
            owner_id, 
            workspace_id, 
            resource_type_id, 
            role_id, 
            \"action\", 
            date_log, 
            short_date_log, 
            details, 
            doer_type, 
            doer_ip, 
            tool_name, 
            resourceNode_id 
            FROM __temp__claro_log
        ");
        $this->addSql("
            DROP TABLE __temp__claro_log
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F12D3860F ON claro_log (doer_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FCD53EDB6 ON claro_log (receiver_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FC6F122B2 ON claro_log (receiver_group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F7E3C61F9 ON claro_log (owner_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F82D40A1F ON claro_log (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FB87FAB32 ON claro_log (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F98EC6B7B ON claro_log (resource_type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FD60322AC ON claro_log (role_id)
        ");
    }
}