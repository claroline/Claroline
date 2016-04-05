<?php

namespace HeVinci\FavouriteBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/20 01:58:39
 */
class Version20150420135837 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_55DB0452A76ED3951BAD783F
        ");
        $this->addSql("
            DROP INDEX IDX_55DB0452A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_55DB04521BAD783F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__hevinci_favourite AS 
            SELECT id, 
            resource_node_id, 
            user_id 
            FROM hevinci_favourite
        ");
        $this->addSql("
            DROP TABLE hevinci_favourite
        ");
        $this->addSql("
            CREATE TABLE hevinci_favourite (
                id INTEGER NOT NULL, 
                resource_node_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_55DB04521BAD783F FOREIGN KEY (resource_node_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_55DB0452A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO hevinci_favourite (id, resource_node_id, user_id) 
            SELECT id, 
            resource_node_id, 
            user_id 
            FROM __temp__hevinci_favourite
        ");
        $this->addSql("
            DROP TABLE __temp__hevinci_favourite
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_55DB0452A76ED3951BAD783F ON hevinci_favourite (user_id, resource_node_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_55DB0452A76ED395 ON hevinci_favourite (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_55DB04521BAD783F ON hevinci_favourite (resource_node_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_55DB0452A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_55DB04521BAD783F
        ");
        $this->addSql("
            DROP INDEX UNIQ_55DB0452A76ED3951BAD783F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__hevinci_favourite AS 
            SELECT id, 
            user_id, 
            resource_node_id 
            FROM hevinci_favourite
        ");
        $this->addSql("
            DROP TABLE hevinci_favourite
        ");
        $this->addSql("
            CREATE TABLE hevinci_favourite (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                resource_node_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_55DB0452A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_55DB04521BAD783F FOREIGN KEY (resource_node_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO hevinci_favourite (id, user_id, resource_node_id) 
            SELECT id, 
            user_id, 
            resource_node_id 
            FROM __temp__hevinci_favourite
        ");
        $this->addSql("
            DROP TABLE __temp__hevinci_favourite
        ");
        $this->addSql("
            CREATE INDEX IDX_55DB0452A76ED395 ON hevinci_favourite (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_55DB04521BAD783F ON hevinci_favourite (resource_node_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_55DB0452A76ED3951BAD783F ON hevinci_favourite (user_id, resource_node_id)
        ");
    }
}