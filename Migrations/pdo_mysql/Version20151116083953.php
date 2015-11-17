<?php

namespace Claroline\ChatBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/11/16 08:39:56
 */
class Version20151116083953 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_chatbundle_room_resource 
            ADD room_type INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_chatbundle_room_message 
            ADD message_type INT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_chatbundle_room_message 
            DROP message_type
        ");
        $this->addSql("
            ALTER TABLE claro_chatbundle_room_resource 
            DROP room_type
        ");
    }
}