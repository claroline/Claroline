<?php

namespace HeVinci\FavouriteBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/01 02:34:05
 */
class Version20150401143404 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_favourite (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                resource_node_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
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
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE hevinci_favourite
        ");
    }
}