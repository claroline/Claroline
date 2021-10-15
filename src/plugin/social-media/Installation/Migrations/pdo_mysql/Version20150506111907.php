<?php

namespace Icap\SocialmediaBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 09:09:42
 */
class Version20150506111907 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE icap__socialmedia_comment (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                text LONGTEXT DEFAULT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                INDEX IDX_6FC00C3089329D25 (resource_id), 
                INDEX IDX_6FC00C30A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE icap__socialmedia_like (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                INDEX IDX_7C98AD9089329D25 (resource_id), 
                INDEX IDX_7C98AD90A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE icap__socialmedia_note (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                text LONGTEXT DEFAULT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                INDEX IDX_1F46173789329D25 (resource_id), 
                INDEX IDX_1F461737A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE icap__socialmedia_share (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                network VARCHAR(255) DEFAULT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                INDEX IDX_4DB117C589329D25 (resource_id), 
                INDEX IDX_4DB117C5A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE icap__socialmedia_comment 
            ADD CONSTRAINT FK_6FC00C3089329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__socialmedia_comment 
            ADD CONSTRAINT FK_6FC00C30A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__socialmedia_like 
            ADD CONSTRAINT FK_7C98AD9089329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__socialmedia_like 
            ADD CONSTRAINT FK_7C98AD90A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__socialmedia_note 
            ADD CONSTRAINT FK_1F46173789329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__socialmedia_note 
            ADD CONSTRAINT FK_1F461737A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__socialmedia_share 
            ADD CONSTRAINT FK_4DB117C589329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__socialmedia_share 
            ADD CONSTRAINT FK_4DB117C5A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE icap__socialmedia_comment
        ');
        $this->addSql('
            DROP TABLE icap__socialmedia_like
        ');
        $this->addSql('
            DROP TABLE icap__socialmedia_note
        ');
        $this->addSql('
            DROP TABLE icap__socialmedia_share
        ');
    }
}
