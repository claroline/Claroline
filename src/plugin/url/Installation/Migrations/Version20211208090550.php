<?php

namespace HeVinci\UrlBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 03:03:30
 */
final class Version20211208090550 extends AbstractMigration
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
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_url (
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                url LONGTEXT NOT NULL, 
                mode VARCHAR(255) NOT NULL, 
                ratio DOUBLE PRECISION DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_A3D1D452D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_A3D1D452B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_url 
            ADD CONSTRAINT FK_B256167B8D0C9323 FOREIGN KEY (tab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_url 
            ADD CONSTRAINT FK_A3D1D452B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab_url 
            DROP FOREIGN KEY FK_B256167B8D0C9323
        ');
        $this->addSql('
            ALTER TABLE hevinci_url 
            DROP FOREIGN KEY FK_A3D1D452B87FAB32
        ');
        $this->addSql('
            DROP TABLE claro_home_tab_url
        ');
        $this->addSql('
            DROP TABLE hevinci_url
        ');
    }
}
