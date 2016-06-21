<?php

namespace Claroline\MessageBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/04/29 11:40:10
 */
class Version20150429114010 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_message (
                id INT AUTO_INCREMENT NOT NULL, 
                sender_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                object VARCHAR(255) NOT NULL, 
                content LONGTEXT NOT NULL, 
                date DATETIME NOT NULL, 
                is_removed TINYINT(1) NOT NULL, 
                lft INT NOT NULL, 
                lvl INT NOT NULL, 
                rgt INT NOT NULL, 
                root INT DEFAULT NULL, 
                sender_username VARCHAR(255) NOT NULL, 
                receiver_string VARCHAR(16000) NOT NULL, 
                INDEX IDX_D6FE8DD8F624B39D (sender_id), 
                INDEX IDX_D6FE8DD8727ACA70 (parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_user_message (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                message_id INT NOT NULL, 
                is_removed TINYINT(1) NOT NULL, 
                is_read TINYINT(1) NOT NULL, 
                is_sent TINYINT(1) NOT NULL, 
                INDEX IDX_D48EA38AA76ED395 (user_id), 
                INDEX IDX_D48EA38A537A1329 (message_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_message 
            ADD CONSTRAINT FK_D6FE8DD8F624B39D FOREIGN KEY (sender_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_message 
            ADD CONSTRAINT FK_D6FE8DD8727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_message (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_user_message 
            ADD CONSTRAINT FK_D48EA38AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_user_message 
            ADD CONSTRAINT FK_D48EA38A537A1329 FOREIGN KEY (message_id) 
            REFERENCES claro_message (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_message 
            DROP FOREIGN KEY FK_D6FE8DD8727ACA70
        ');
        $this->addSql('
            ALTER TABLE claro_user_message 
            DROP FOREIGN KEY FK_D48EA38A537A1329
        ');
        $this->addSql('
            DROP TABLE claro_message
        ');
        $this->addSql('
            DROP TABLE claro_user_message
        ');
    }
}
