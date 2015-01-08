<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/01/07 09:43:43
 */
class Version20150107094341 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_personnal_workspace_tool_config (
                id INT IDENTITY NOT NULL, 
                role_id INT NOT NULL, 
                tool_id INT NOT NULL, 
                mask INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_7A4A6A64D60322AC ON claro_personnal_workspace_tool_config (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_7A4A6A648F7B22CC ON claro_personnal_workspace_tool_config (tool_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX pws_unique_tool_config ON claro_personnal_workspace_tool_config (tool_id, role_id) 
            WHERE tool_id IS NOT NULL 
            AND role_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_personal_workspace_resource_rights_management_access (
                id INT IDENTITY NOT NULL, 
                role_id INT NOT NULL, 
                is_accessible BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A3AE069AD60322AC ON claro_personal_workspace_resource_rights_management_access (role_id)
        ");
        $this->addSql("
            ALTER TABLE claro_personnal_workspace_tool_config 
            ADD CONSTRAINT FK_7A4A6A64D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_personnal_workspace_tool_config 
            ADD CONSTRAINT FK_7A4A6A648F7B22CC FOREIGN KEY (tool_id) 
            REFERENCES claro_tools (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_personal_workspace_resource_rights_management_access 
            ADD CONSTRAINT FK_A3AE069AD60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_EB8D285282D40A1F ON claro_user (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FFAA23F6C8 ON claro_resource_node (next_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF2DE62210 ON claro_resource_node (previous_id)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD maxStorageSize NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD maxUploadResources INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD is_personal BIT NOT NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8B87FAB32 ON claro_resource_shortcut (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD is_upload_destination BIT NOT NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_12EEC186B87FAB32 ON claro_directory (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E2EE25E281C06096 ON claro_activity_parameters (activity_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EA81C80BB87FAB32 ON claro_file (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5D9559DCB87FAB32 ON claro_text (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E4A67CAC88BD9C1F ON claro_activity (parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_E4A67CACB87FAB32 ON claro_activity (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_personnal_workspace_tool_config
        ");
        $this->addSql("
            DROP TABLE claro_personal_workspace_resource_rights_management_access
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_E4A67CAC88BD9C1F'
            ) 
            ALTER TABLE claro_activity 
            DROP CONSTRAINT IDX_E4A67CAC88BD9C1F ELSE 
            DROP INDEX IDX_E4A67CAC88BD9C1F ON claro_activity
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_E4A67CACB87FAB32'
            ) 
            ALTER TABLE claro_activity 
            DROP CONSTRAINT IDX_E4A67CACB87FAB32 ELSE 
            DROP INDEX IDX_E4A67CACB87FAB32 ON claro_activity
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_E2EE25E281C06096'
            ) 
            ALTER TABLE claro_activity_parameters 
            DROP CONSTRAINT IDX_E2EE25E281C06096 ELSE 
            DROP INDEX IDX_E2EE25E281C06096 ON claro_activity_parameters
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_12EEC186B87FAB32'
            ) 
            ALTER TABLE claro_directory 
            DROP CONSTRAINT IDX_12EEC186B87FAB32 ELSE 
            DROP INDEX IDX_12EEC186B87FAB32 ON claro_directory
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP COLUMN is_upload_destination
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_EA81C80BB87FAB32'
            ) 
            ALTER TABLE claro_file 
            DROP CONSTRAINT IDX_EA81C80BB87FAB32 ELSE 
            DROP INDEX IDX_EA81C80BB87FAB32 ON claro_file
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_A76799FFAA23F6C8'
            ) 
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT IDX_A76799FFAA23F6C8 ELSE 
            DROP INDEX IDX_A76799FFAA23F6C8 ON claro_resource_node
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_A76799FF2DE62210'
            ) 
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT IDX_A76799FF2DE62210 ELSE 
            DROP INDEX IDX_A76799FF2DE62210 ON claro_resource_node
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_5E7F4AB8B87FAB32'
            ) 
            ALTER TABLE claro_resource_shortcut 
            DROP CONSTRAINT IDX_5E7F4AB8B87FAB32 ELSE 
            DROP INDEX IDX_5E7F4AB8B87FAB32 ON claro_resource_shortcut
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_5D9559DCB87FAB32'
            ) 
            ALTER TABLE claro_text 
            DROP CONSTRAINT IDX_5D9559DCB87FAB32 ELSE 
            DROP INDEX IDX_5D9559DCB87FAB32 ON claro_text
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_EB8D285282D40A1F'
            ) 
            ALTER TABLE claro_user 
            DROP CONSTRAINT IDX_EB8D285282D40A1F ELSE 
            DROP INDEX IDX_EB8D285282D40A1F ON claro_user
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN maxStorageSize
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN maxUploadResources
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN is_personal
        ");
    }
}