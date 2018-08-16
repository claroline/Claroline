<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/08/15 01:59:45
 */
class Version20180815135927 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory 
            ADD show_summary TINYINT(1) DEFAULT "1" NOT NULL, 
            ADD open_summary TINYINT(1) DEFAULT "1" NOT NULL, 
            ADD filterable TINYINT(1) NOT NULL, 
            ADD sortable TINYINT(1) NOT NULL, 
            ADD paginated TINYINT(1) NOT NULL, 
            ADD sortBy VARCHAR(255) DEFAULT NULL, 
            ADD pageSize INT NOT NULL, 
            ADD display VARCHAR(255) NOT NULL, 
            ADD availableDisplays LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)", 
            ADD filters LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)", 
            ADD availableColumns LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)", 
            ADD displayedColumns LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list 
            ADD sortBy VARCHAR(255) DEFAULT NULL, 
            CHANGE defaultFilters filters LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)"
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory 
            DROP show_summary, 
            DROP open_summary, 
            DROP filterable, 
            DROP sortable, 
            DROP paginated, 
            DROP sortBy, 
            DROP pageSize, 
            DROP display, 
            DROP availableDisplays, 
            DROP filters, 
            DROP availableColumns, 
            DROP displayedColumns
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list 
            DROP sortBy, 
            CHANGE filters defaultFilters LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT "(DC2Type:json_array)"
        ');
    }
}
