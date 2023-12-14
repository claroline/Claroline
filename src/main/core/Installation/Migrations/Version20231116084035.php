<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/11/16 08:40:47
 */
final class Version20231116084035 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_tool_mask_decoder 
            DROP FOREIGN KEY FK_323623448F7B22CC
        ');
        $this->addSql('
            DROP INDEX IDX_323623448F7B22CC ON claro_tool_mask_decoder
        ');
        $this->addSql('
            DROP INDEX tool_mask_decoder_unique_tool_and_name ON claro_tool_mask_decoder
        ');
        $this->addSql('
            ALTER TABLE claro_tool_mask_decoder 
            ADD tool_name VARCHAR(255) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_tool_mask_decoder AS m
            LEFT JOIN claro_tools AS t ON m.tool_id = t.id
            SET m.tool_name = t.name
        ');

        $this->addSql('
            ALTER TABLE claro_tool_mask_decoder
            DROP tool_id
        ');

        $this->addSql('
            CREATE UNIQUE INDEX tool_mask_decoder_unique_tool_and_name ON claro_tool_mask_decoder (tool_name, `name`)
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            DROP FOREIGN KEY FK_6CF1320E8F7B22CC
        ');
        $this->addSql('
            DROP INDEX IDX_6CF1320E8F7B22CC ON claro_ordered_tool
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            ADD tool_name VARCHAR(255) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_ordered_tool AS ot
            LEFT JOIN claro_tools AS t ON ot.tool_id = t.id
            SET ot.tool_name = t.name
        ');

        $this->addSql('
            ALTER TABLE claro_ordered_tool
            DROP tool_id
        ');

        $this->addSql('
            ALTER TABLE claro_tools 
            DROP is_displayable_in_workspace, 
            DROP is_displayable_in_desktop
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_tools 
            ADD is_displayable_in_workspace TINYINT(1) NOT NULL, 
            ADD is_displayable_in_desktop TINYINT(1) NOT NULL
        ');

        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            ADD tool_id INT NOT NULL, 
            DROP tool_name
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            ADD CONSTRAINT FK_6CF1320E8F7B22CC FOREIGN KEY (tool_id) 
            REFERENCES claro_tools (id) ON UPDATE NO ACTION 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_6CF1320E8F7B22CC ON claro_ordered_tool (tool_id)
        ');
        $this->addSql('
            DROP INDEX tool_mask_decoder_unique_tool_and_name ON claro_tool_mask_decoder
        ');
        $this->addSql('
            ALTER TABLE claro_tool_mask_decoder 
            ADD tool_id INT NOT NULL, 
            DROP tool_name
        ');
        $this->addSql('
            ALTER TABLE claro_tool_mask_decoder 
            ADD CONSTRAINT FK_323623448F7B22CC FOREIGN KEY (tool_id) 
            REFERENCES claro_tools (id) ON UPDATE NO ACTION 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_323623448F7B22CC ON claro_tool_mask_decoder (tool_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX tool_mask_decoder_unique_tool_and_name ON claro_tool_mask_decoder (tool_id, name)
        ');
    }
}
