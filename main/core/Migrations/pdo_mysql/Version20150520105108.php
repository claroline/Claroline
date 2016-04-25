<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/05/20 10:51:09
 */
class Version20150520105108 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_contact_category (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                category_name VARCHAR(255) NOT NULL, 
                category_order INT NOT NULL, 
                INDEX IDX_2C48C9BBA76ED395 (user_id), 
                UNIQUE INDEX contact_unique_user_category (user_id, category_name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_contact_options (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                options LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                UNIQUE INDEX UNIQ_BBCE147CA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE claro_contact (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                contact_id INT DEFAULT NULL, 
                INDEX IDX_2C215B9FA76ED395 (user_id), 
                INDEX IDX_2C215B9FE7A1254A (contact_id), 
                UNIQUE INDEX contact_unique_user_contact (user_id, contact_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_contact_categories (
                contact_id INT NOT NULL, 
                category_id INT NOT NULL, 
                INDEX IDX_69F02FC4E7A1254A (contact_id), 
                INDEX IDX_69F02FC412469DE2 (category_id), 
                PRIMARY KEY(contact_id, category_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_contact_category 
            ADD CONSTRAINT FK_2C48C9BBA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_contact_options 
            ADD CONSTRAINT FK_BBCE147CA76ED395 FOREIGN KEY (user_id) 
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
            DROP TABLE claro_contact_category
        ');
        $this->addSql('
            DROP TABLE claro_contact_options
        ');
        $this->addSql('
            DROP TABLE claro_contact
        ');
        $this->addSql('
            DROP TABLE claro_contact_categories
        ');
    }
}
