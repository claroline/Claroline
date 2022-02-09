<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2022/02/01 06:24:30
 */
class Version20220201062416 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_user CHANGE last_login last_activity DATETIME DEFAULT NULL
        ');

        $this->addSql('
            UPDATE claro_widget_list AS l
            LEFT JOIN claro_widget_instance AS i ON (l.widgetInstance_id = i.id)
            LEFT JOIN claro_data_source AS s ON (i.dataSource_id = s.id)
            SET 
                availableSort = REPLACE(availableSort, "lastLogin", "lastActivity"),
                sortBy = REPLACE(sortBy, "lastLogin", "lastActivity"),
                availableColumns = REPLACE(availableColumns, "lastLogin", "lastActivity"),
                displayedColumns = REPLACE(displayedColumns, "lastLogin", "lastActivity")
            WHERE s.source_name = "users"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_user CHANGE last_activity last_login DATETIME DEFAULT NULL
        ');
    }
}
