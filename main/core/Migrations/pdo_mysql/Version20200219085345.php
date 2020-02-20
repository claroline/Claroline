<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/02/19 08:54:00
 */
class Version20200219085345 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_connection_message_slide 
            ADD shortcuts LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json_array)"
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_connection_message_slide 
            DROP shortcuts
        ');
    }
}
