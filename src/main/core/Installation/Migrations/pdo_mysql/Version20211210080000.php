<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/12/10 08:00:00
 */
class Version20211210080000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE claro_widget_list AS l
            LEFT JOIN claro_widget_instance AS i ON (l.widgetInstance_id = i.id)
            LEFT JOIN claro_data_source AS s ON (i.dataSource_id = s.id)
            SET 
                availableSort = REPLACE(availableSort, "\"created\"", "\"createdAt\""),
                sortBy = REPLACE(sortBy, "created", "createdAt"),
                availableColumns = REPLACE(availableColumns, "\"created\"", "\"createdAt\""),
                displayedColumns = REPLACE(displayedColumns, "\"created\"", "\"createdAt\"")
            WHERE s.source_name = "workspaces" OR s.source_name = "my_workspaces"
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
