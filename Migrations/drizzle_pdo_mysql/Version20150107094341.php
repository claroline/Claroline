<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/01/07 09:43:42
 */
class Version20150107094341 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_personnal_workspace_tool_config (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                tool_id INT NOT NULL, 
                mask INT NOT NULL, 
                INDEX IDX_7A4A6A64D60322AC (role_id), 
                INDEX IDX_7A4A6A648F7B22CC (tool_id), 
                UNIQUE INDEX pws_unique_tool_config (tool_id, role_id), 
                PRIMARY KEY(id)
            ) COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_personal_workspace_resource_rights_management_access (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                is_accessible BOOLEAN NOT NULL, 
                INDEX IDX_A3AE069AD60322AC (role_id), 
                PRIMARY KEY(id)
            ) COLLATE utf8_unicode_ci ENGINE = InnoDB
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
            ADD maxStorageSize VARCHAR(255) NOT NULL, 
            ADD maxUploadResources INT NOT NULL, 
            ADD is_personal BOOLEAN NOT NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8B87FAB32 ON claro_resource_shortcut (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            ADD is_upload_destination BOOLEAN NOT NULL
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
            DROP INDEX IDX_E4A67CAC88BD9C1F ON claro_activity
        ");
        $this->addSql("
            DROP INDEX IDX_E4A67CACB87FAB32 ON claro_activity
        ");
        $this->addSql("
            DROP INDEX IDX_E2EE25E281C06096 ON claro_activity_parameters
        ");
        $this->addSql("
            DROP INDEX IDX_12EEC186B87FAB32 ON claro_directory
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP is_upload_destination
        ");
        $this->addSql("
            DROP INDEX IDX_EA81C80BB87FAB32 ON claro_file
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FFAA23F6C8 ON claro_resource_node
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF2DE62210 ON claro_resource_node
        ");
        $this->addSql("
            DROP INDEX IDX_5E7F4AB8B87FAB32 ON claro_resource_shortcut
        ");
        $this->addSql("
            DROP INDEX IDX_5D9559DCB87FAB32 ON claro_text
        ");
        $this->addSql("
            DROP INDEX IDX_EB8D285282D40A1F ON claro_user
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP maxStorageSize, 
            DROP maxUploadResources, 
            DROP is_personal
        ");
    }
}