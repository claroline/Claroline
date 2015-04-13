<?php

namespace HeVinci\FavouriteBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/01 02:34:06
 */
class Version20150401143404 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_favourite (
                id INT IDENTITY NOT NULL, 
                user_id INT, 
                resource_node_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_55DB0452A76ED395 ON hevinci_favourite (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_55DB04521BAD783F ON hevinci_favourite (resource_node_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_55DB0452A76ED3951BAD783F ON hevinci_favourite (user_id, resource_node_id) 
            WHERE user_id IS NOT NULL 
            AND resource_node_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_favourite 
            ADD CONSTRAINT FK_55DB0452A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE hevinci_favourite 
            ADD CONSTRAINT FK_55DB04521BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE hevinci_favourite
        ");
    }
}