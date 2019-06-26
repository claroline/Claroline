<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/06/24 03:01:13
 */
class Version20190624150111 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_connection_message_slide (
                id INT AUTO_INCREMENT NOT NULL, 
                message_id INT DEFAULT NULL, 
                content LONGTEXT DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                picture VARCHAR(255) DEFAULT NULL, 
                slide_order INT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_DBB5C281D17F50A6 (uuid), 
                INDEX IDX_DBB5C281537A1329 (message_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_connection_message (
                id INT AUTO_INCREMENT NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                message_type VARCHAR(255) NOT NULL, 
                start_date DATETIME NOT NULL, 
                end_date DATETIME NOT NULL, 
                locked TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_590DE667D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_connection_message_role (
                connectionmessage_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_B1EB3A86E926F912 (connectionmessage_id), 
                INDEX IDX_B1EB3A86D60322AC (role_id), 
                PRIMARY KEY(connectionmessage_id, role_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_connection_message_user (
                connectionmessage_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_6B1166A5E926F912 (connectionmessage_id), 
                INDEX IDX_6B1166A5A76ED395 (user_id), 
                PRIMARY KEY(connectionmessage_id, user_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_slide 
            ADD CONSTRAINT FK_DBB5C281537A1329 FOREIGN KEY (message_id) 
            REFERENCES claro_connection_message (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_role 
            ADD CONSTRAINT FK_B1EB3A86E926F912 FOREIGN KEY (connectionmessage_id) 
            REFERENCES claro_connection_message (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_role 
            ADD CONSTRAINT FK_B1EB3A86D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_user 
            ADD CONSTRAINT FK_6B1166A5E926F912 FOREIGN KEY (connectionmessage_id) 
            REFERENCES claro_connection_message (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_user 
            ADD CONSTRAINT FK_6B1166A5A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_A76799FF989D9B62 ON claro_resource_node (slug)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_connection_message_slide 
            DROP FOREIGN KEY FK_DBB5C281537A1329
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_role 
            DROP FOREIGN KEY FK_B1EB3A86E926F912
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_user 
            DROP FOREIGN KEY FK_6B1166A5E926F912
        ');
        $this->addSql('
            DROP TABLE claro_connection_message_slide
        ');
        $this->addSql('
            DROP TABLE claro_connection_message
        ');
        $this->addSql('
            DROP TABLE claro_connection_message_role
        ');
        $this->addSql('
            DROP TABLE claro_connection_message_user
        ');
        $this->addSql('
            DROP INDEX UNIQ_A76799FF989D9B62 ON claro_resource_node
        ');
    }
}
