<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/20 05:19:24
 */
class Version20150420171923 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            ADD COLUMN content_id INTEGER NOT NULL 
            DROP COLUMN name
        ");
        $this->addSql("
            DROP INDEX ordered_tool_unique_name_by_workspace
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            ADD CONSTRAINT FK_6CF1320E84A0A3ED FOREIGN KEY (content_id) 
            REFERENCES claro_content (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_6CF1320E84A0A3ED ON claro_ordered_tool (content_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            ADD COLUMN name VARCHAR(255) NOT NULL 
            DROP COLUMN content_id
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            DROP FOREIGN KEY FK_6CF1320E84A0A3ED
        ");
        $this->addSql("
            DROP INDEX IDX_6CF1320E84A0A3ED
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_name_by_workspace ON claro_ordered_tool (workspace_id, name)
        ");
    }
}