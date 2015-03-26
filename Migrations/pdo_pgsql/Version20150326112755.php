<?php

namespace HeVinci\UrlBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/26 11:27:57
 */
class Version20150326112755 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_url (
                id SERIAL NOT NULL, 
                url VARCHAR(255) NOT NULL, 
                internal_url BOOLEAN NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A3D1D452B87FAB32 ON hevinci_url (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE hevinci_url 
            ADD CONSTRAINT FK_A3D1D452B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE hevinci_url
        ");
    }
}