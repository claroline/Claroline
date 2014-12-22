<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/12/19 02:46:42
 */
class Version20141219144641 extends AbstractMigration
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
            ALTER TABLE claro_directory 
            ADD is_upload_destination BIT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_personnal_workspace_tool_config
        ");
        $this->addSql("
            ALTER TABLE claro_directory 
            DROP COLUMN is_upload_destination
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