<?php

namespace Claroline\ClacoFormBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/11/07 09:40:29
 */
class Version20181107093944 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_claco_form 
            ADD filterable TINYINT(1) NOT NULL, 
            ADD sortable TINYINT(1) NOT NULL, 
            ADD paginated TINYINT(1) NOT NULL, 
            ADD columnsFilterable TINYINT(1) NOT NULL, 
            ADD count TINYINT(1) NOT NULL, 
            ADD actions TINYINT(1) NOT NULL, 
            ADD sortBy VARCHAR(255) DEFAULT NULL, 
            ADD availableSort LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)", 
            ADD pageSize INT NOT NULL, 
            ADD availablePageSizes LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)", 
            ADD display VARCHAR(255) NOT NULL, 
            ADD availableDisplays LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)", 
            ADD searchMode VARCHAR(255) DEFAULT NULL, 
            ADD filters LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)", 
            ADD availableFilters LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)", 
            ADD availableColumns LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)", 
            ADD displayedColumns LONGTEXT NOT NULL COMMENT "(DC2Type:json_array)", 
            ADD card LONGTEXT NOT NULL COMMENT "(DC2Type:json)"
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_claco_form 
            DROP filterable, 
            DROP sortable, 
            DROP paginated, 
            DROP columnsFilterable, 
            DROP count, 
            DROP actions, 
            DROP sortBy, 
            DROP availableSort, 
            DROP pageSize, 
            DROP availablePageSizes, 
            DROP display, 
            DROP availableDisplays, 
            DROP searchMode, 
            DROP filters, 
            DROP availableFilters, 
            DROP availableColumns, 
            DROP displayedColumns, 
            DROP card
        ');
    }
}
