<?php

namespace HeVinci\UrlBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/11/20 08:48:54
 */
class Version20201120084833 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_home_tab_url (
                id INT AUTO_INCREMENT NOT NULL, 
                tab_id INT DEFAULT NULL, 
                url LONGTEXT NOT NULL, 
                mode VARCHAR(255) NOT NULL, 
                ratio DOUBLE PRECISION DEFAULT NULL, 
                UNIQUE INDEX UNIQ_B256167B8D0C9323 (tab_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_url 
            ADD CONSTRAINT FK_B256167B8D0C9323 FOREIGN KEY (tab_id) 
            REFERENCES claro_home_tab (id) ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_url 
            DROP internal_url, 
            CHANGE url url LONGTEXT NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE claro_home_tab_url
        ');
        $this->addSql('
            ALTER TABLE hevinci_url 
            ADD internal_url TINYINT(1) NOT NULL, 
            CHANGE url url VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`
        ');
    }
}
