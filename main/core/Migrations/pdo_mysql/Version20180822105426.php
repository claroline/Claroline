<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/08/22 10:54:28
 */
class Version20180822105426 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory 
            ADD availablePageSizes LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)",
            ADD availableFilters LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list 
            ADD availablePageSizes LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)",
            ADD availableFilters LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)"
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory
            DROP availablePageSizes,
            DROP availableFilters
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list
            DROP availablePageSizes,
            DROP availableFilters
        ');
    }
}
