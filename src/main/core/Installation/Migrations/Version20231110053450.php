<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/11/10 05:35:05
 */
final class Version20231110053450 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            ADD context_name VARCHAR(255) NOT NULL, 
            ADD context_id VARCHAR(255) DEFAULT NULL
        ');

        // migrate desktop tools
        $this->addSql('
            UPDATE claro_ordered_tool SET context_name = "desktop" WHERE workspace_id IS NULL 
        ');

        // migrate workspace tools
        $this->addSql('
            UPDATE claro_ordered_tool AS t 
            LEFT JOIN claro_workspace AS w ON t.workspace_id = w.id
            SET context_name = "workspace", context_id = w.uuid WHERE workspace_id IS NOT NULL 
        ');

        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            DROP FOREIGN KEY FK_6CF1320E82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            DROP FOREIGN KEY FK_6CF1320EA76ED395
        ');
        $this->addSql('
            DROP INDEX ordered_tool_unique_tool_user_type ON claro_ordered_tool
        ');
        $this->addSql('
            DROP INDEX ordered_tool_unique_tool_ws_type ON claro_ordered_tool
        ');
        $this->addSql('
            DROP INDEX IDX_6CF1320E82D40A1F ON claro_ordered_tool
        ');
        $this->addSql('
            DROP INDEX IDX_6CF1320EA76ED395 ON claro_ordered_tool
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool
            DROP workspace_id,
            DROP user_id
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            ADD workspace_id INT DEFAULT NULL, 
            ADD user_id INT DEFAULT NULL, 
            DROP context_name, 
            DROP context_id
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            ADD CONSTRAINT FK_6CF1320E82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) ON UPDATE NO ACTION 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            ADD CONSTRAINT FK_6CF1320EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) ON UPDATE NO ACTION 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX ordered_tool_unique_tool_user_type ON claro_ordered_tool (tool_id, user_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX ordered_tool_unique_tool_ws_type ON claro_ordered_tool (tool_id, workspace_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_6CF1320E82D40A1F ON claro_ordered_tool (workspace_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_6CF1320EA76ED395 ON claro_ordered_tool (user_id)
        ');
    }
}
