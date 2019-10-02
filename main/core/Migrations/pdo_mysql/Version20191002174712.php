<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/10/02 05:47:39
 */
class Version20191002174712 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            DROP INDEX ordered_tool_unique_name_by_workspace ON claro_ordered_tool
        ');
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            DROP name
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_ordered_tool 
            ADD name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            CREATE UNIQUE INDEX ordered_tool_unique_name_by_workspace ON claro_ordered_tool (workspace_id, name)
        ');
    }
}
