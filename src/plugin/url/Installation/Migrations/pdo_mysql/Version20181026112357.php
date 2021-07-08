<?php

namespace HeVinci\UrlBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 09:11:07
 */
class Version20181026112357 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE hevinci_url (
                id INT AUTO_INCREMENT NOT NULL, 
                url VARCHAR(255) NOT NULL, 
                mode VARCHAR(255) NOT NULL, 
                internal_url TINYINT(1) NOT NULL, 
                ratio DOUBLE PRECISION DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_A3D1D452B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
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
            DROP TABLE hevinci_url
        ');
    }
}
