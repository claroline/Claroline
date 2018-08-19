<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/08/19 08:57:53
 */
class Version20180819085751 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_directory CHANGE sortBy sortBy VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_data_source 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE claro_data_source SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_B4A87F0BD17F50A6 ON claro_data_source (uuid)
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list CHANGE sortBy sortBy VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_B4A87F0BD17F50A6 ON claro_data_source
        ');
        $this->addSql('
            ALTER TABLE claro_data_source 
            DROP uuid
        ');
        $this->addSql('
            ALTER TABLE claro_directory CHANGE sortBy sortBy VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE claro_widget_list CHANGE sortBy sortBy VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ');
    }
}
