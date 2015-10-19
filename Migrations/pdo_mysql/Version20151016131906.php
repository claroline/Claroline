<?php

namespace Claroline\ChatBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/10/16 01:19:08
 */
class Version20151016131906 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_chatbundle_chat_user (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                chat_username VARCHAR(255) NOT NULL, 
                chat_password VARCHAR(255) NOT NULL, 
                options LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                UNIQUE INDEX UNIQ_63EF42F2CC7CD147 (chat_username), 
                UNIQUE INDEX UNIQ_63EF42F2A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_chatbundle_chat_user 
            ADD CONSTRAINT FK_63EF42F2A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_chatbundle_chat_user
        ");
    }
}