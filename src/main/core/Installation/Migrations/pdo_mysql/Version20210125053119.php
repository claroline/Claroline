<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/01/25 05:31:20
 */
class Version20210125053119 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory CHANGE availableSort availableSort LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
            CHANGE availablePageSizes availablePageSizes LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
            CHANGE availableDisplays availableDisplays LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
            CHANGE filters filters LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
            CHANGE availableFilters availableFilters LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
            CHANGE availableColumns availableColumns LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
            CHANGE displayedColumns displayedColumns LONGTEXT NOT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list CHANGE availableSort availableSort LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
            CHANGE availablePageSizes availablePageSizes LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
            CHANGE availableDisplays availableDisplays LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
            CHANGE filters filters LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
            CHANGE availableFilters availableFilters LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
            CHANGE availableColumns availableColumns LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
            CHANGE displayedColumns displayedColumns LONGTEXT NOT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            ALTER TABLE claro_workspace CHANGE allowed_ips allowed_ips LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory CHANGE availableSort availableSort LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE availablePageSizes availablePageSizes LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE availableDisplays availableDisplays LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE filters filters LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE availableFilters availableFilters LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE availableColumns availableColumns LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE displayedColumns displayedColumns LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list CHANGE availableSort availableSort LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE availablePageSizes availablePageSizes LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE availableDisplays availableDisplays LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE filters filters LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE availableFilters availableFilters LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE availableColumns availableColumns LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE displayedColumns displayedColumns LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro_workspace CHANGE allowed_ips allowed_ips LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
    }
}
