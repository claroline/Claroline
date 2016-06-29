<?php

namespace Claroline\ChatBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2016/04/14 04:26:30
 */
class Version20160414162629 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_chatbundle_room_message CHANGE content content LONGTEXT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_chatbundle_room_message CHANGE content content VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci
        ");
    }
}