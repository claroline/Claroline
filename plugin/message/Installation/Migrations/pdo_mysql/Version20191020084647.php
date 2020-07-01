<?php

namespace Claroline\MessageBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 08:59:07
 */
class Version20191020084647 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_contact_options (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                options LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                UNIQUE INDEX UNIQ_BBCE147CA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_contact_category (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                category_name VARCHAR(255) NOT NULL, 
                category_order INT NOT NULL, 
                INDEX IDX_2C48C9BBA76ED395 (user_id), 
                UNIQUE INDEX contact_unique_user_category (user_id, category_name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_contact (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                contact_id INT DEFAULT NULL, 
                INDEX IDX_2C215B9FA76ED395 (user_id), 
                INDEX IDX_2C215B9FE7A1254A (contact_id), 
                UNIQUE INDEX contact_unique_user_contact (user_id, contact_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_contact_categories (
                contact_id INT NOT NULL, 
                category_id INT NOT NULL, 
                INDEX IDX_69F02FC4E7A1254A (contact_id), 
                INDEX IDX_69F02FC412469DE2 (category_id), 
                PRIMARY KEY(contact_id, category_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_message (
                id INT AUTO_INCREMENT NOT NULL, 
                sender_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                object VARCHAR(255) NOT NULL, 
                content LONGTEXT NOT NULL, 
                date DATETIME NOT NULL, 
                lft INT NOT NULL, 
                lvl INT NOT NULL, 
                rgt INT NOT NULL, 
                root INT DEFAULT NULL, 
                sender_username VARCHAR(255) NOT NULL, 
                receiver_string VARCHAR(16000) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_D6FE8DD8D17F50A6 (uuid), 
                INDEX IDX_D6FE8DD8F624B39D (sender_id), 
                INDEX IDX_D6FE8DD8727ACA70 (parent_id), 
                INDEX level_idx (lvl), 
                INDEX root_idx (root), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_user_message (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                message_id INT NOT NULL, 
                is_removed TINYINT(1) NOT NULL, 
                is_read TINYINT(1) NOT NULL, 
                is_sent TINYINT(1) NOT NULL, 
                last_open_date DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_D48EA38AD17F50A6 (uuid), 
                INDEX IDX_D48EA38AA76ED395 (user_id), 
                INDEX IDX_D48EA38A537A1329 (message_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_contact_options 
            ADD CONSTRAINT FK_BBCE147CA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_contact_category 
            ADD CONSTRAINT FK_2C48C9BBA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_contact 
            ADD CONSTRAINT FK_2C215B9FA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_contact 
            ADD CONSTRAINT FK_2C215B9FE7A1254A FOREIGN KEY (contact_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_contact_categories 
            ADD CONSTRAINT FK_69F02FC4E7A1254A FOREIGN KEY (contact_id) 
            REFERENCES claro_contact (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_contact_categories 
            ADD CONSTRAINT FK_69F02FC412469DE2 FOREIGN KEY (category_id) 
            REFERENCES claro_contact_category (id) 
            ON DELETE CASCADE
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
            ALTER TABLE claro_contact_categories 
            DROP FOREIGN KEY FK_69F02FC412469DE2
        ');
        $this->addSql('
            ALTER TABLE claro_contact_categories 
            DROP FOREIGN KEY FK_69F02FC4E7A1254A
        ');
        $this->addSql('
            ALTER TABLE claro_message 
            DROP FOREIGN KEY FK_D6FE8DD8727ACA70
        ');
        $this->addSql('
            ALTER TABLE claro_user_message 
            DROP FOREIGN KEY FK_D48EA38A537A1329
        ');
        $this->addSql('
            DROP TABLE claro_contact_options
        ');
        $this->addSql('
            DROP TABLE claro_contact_category
        ');
        $this->addSql('
            DROP TABLE claro_contact
        ');
        $this->addSql('
            DROP TABLE claro_contact_categories
        ');
        $this->addSql('
            DROP TABLE claro_message
        ');
        $this->addSql('
            DROP TABLE claro_user_message
        ');
    }
}
