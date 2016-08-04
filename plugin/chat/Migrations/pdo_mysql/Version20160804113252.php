<?php

namespace Claroline\ChatBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/08/04 11:32:53
 */
class Version20160804113252 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_chatbundle_room_resource (
                id INT AUTO_INCREMENT NOT NULL,
                room_name VARCHAR(255) DEFAULT NULL,
                room_status INT NOT NULL,
                room_type INT NOT NULL,
                resourceNode_id INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_DC04C3D0B87FAB32 (resourceNode_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_chatbundle_room_message (
                id INT AUTO_INCREMENT NOT NULL,
                chat_room_id INT NOT NULL,
                username VARCHAR(255) NOT NULL,
                user_full_name VARCHAR(255) NOT NULL,
                content LONGTEXT DEFAULT NULL,
                creation_date DATETIME NOT NULL,
                message_type INT NOT NULL,
                INDEX IDX_930423E01819BCFA (chat_room_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
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
        $this->addSql('
            ALTER TABLE claro_chatbundle_room_resource
            ADD CONSTRAINT FK_DC04C3D0B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_chatbundle_room_message
            ADD CONSTRAINT FK_930423E01819BCFA FOREIGN KEY (chat_room_id)
            REFERENCES claro_chatbundle_room_resource (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_chatbundle_chat_user
            ADD CONSTRAINT FK_63EF42F2A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_chatbundle_room_message
            DROP FOREIGN KEY FK_930423E01819BCFA
        ');
        $this->addSql('
            DROP TABLE claro_chatbundle_room_resource
        ');
        $this->addSql('
            DROP TABLE claro_chatbundle_room_message
        ');
        $this->addSql('
            DROP TABLE claro_chatbundle_chat_user
        ');
    }
}
