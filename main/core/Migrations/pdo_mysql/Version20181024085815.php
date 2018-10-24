<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/10/24 08:59:00
 */
class Version20181024085815 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory 
            ADD card LONGTEXT NOT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            UPDATE claro_directory SET card = "{\"icon\": true, \"flags\": true, \"description\": true, \"footer\": true}"
        ');

        $this->addSql('
            ALTER TABLE claro_widget_list 
            ADD card LONGTEXT NOT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            UPDATE claro_widget_list SET card = "{\"icon\": true, \"flags\": true, \"description\": true, \"footer\": true}"
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory 
            DROP card
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list 
            DROP card
        ');
    }
}
