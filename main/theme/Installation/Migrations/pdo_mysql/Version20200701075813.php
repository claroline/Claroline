<?php

namespace Claroline\ThemeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 07:58:42
 */
class Version20200701075813 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // because tables were originally created by CoreBundle
        $this->addSql('
            DROP TABLE IF EXISTS claro_theme_color_collection
        ');
        $this->addSql('
            DROP TABLE IF EXISTS claro_theme_poster_collection
        ');

        $this->addSql('
            CREATE TABLE claro_theme_color_collection (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                name LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_D195EAE6D17F50A6 (uuid), 
                INDEX IDX_D195EAE6A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_theme_poster_collection (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                name LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_2CD4B836D17F50A6 (uuid), 
                INDEX IDX_2CD4B836A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_theme_color_collection 
            ADD CONSTRAINT FK_D195EAE6A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_theme_poster_collection 
            ADD CONSTRAINT FK_2CD4B836A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE claro_theme_color_collection
        ');
        $this->addSql('
            DROP TABLE claro_theme_poster_collection
        ');
    }
}
