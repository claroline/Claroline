<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/07/03 11:35:54
 */
class Version20190703113552 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_connection_message_slide CHANGE picture poster VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message 
            ADD accessible_from DATETIME DEFAULT NULL, 
            ADD accessible_until DATETIME DEFAULT NULL, 
            DROP start_date, 
            DROP end_date
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_connection_message 
            ADD start_date DATETIME NOT NULL, 
            ADD end_date DATETIME NOT NULL, 
            DROP accessible_from, 
            DROP accessible_until
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_slide CHANGE poster picture VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci
        ');
    }
}
