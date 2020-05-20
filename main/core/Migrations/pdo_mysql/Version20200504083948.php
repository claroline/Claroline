<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/05/04 08:40:27
 */
class Version20200504083948 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_tool_mask_decoder 
            DROP granted_icon_class, 
            DROP denied_icon_class
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_tool_mask_decoder 
            ADD granted_icon_class VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
            ADD denied_icon_class VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ');
    }
}
