<?php

namespace Claroline\ForumBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/09/24 02:59:14
 */
class Version20150924145911 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_forum_last_message_widget_config (
                id INT AUTO_INCREMENT NOT NULL, 
                widget_instance_id INT NOT NULL, 
                display_my_last_messages TINYINT(1) NOT NULL, 
                UNIQUE INDEX UNIQ_F68C6FF444BF891 (widget_instance_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_forum_last_message_widget_config 
            ADD CONSTRAINT FK_F68C6FF444BF891 FOREIGN KEY (widget_instance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_forum_last_message_widget_config
        ');
    }
}
