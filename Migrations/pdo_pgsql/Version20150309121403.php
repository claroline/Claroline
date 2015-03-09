<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/09 12:14:05
 */
class Version20150309121403 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            ADD ordered_tool_type INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            ADD is_locked BOOLEAN NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            DROP ordered_tool_type
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool 
            DROP is_locked
        ");
    }
}