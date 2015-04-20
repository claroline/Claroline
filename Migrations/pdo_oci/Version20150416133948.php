<?php

namespace HeVinci\FavouriteBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/16 01:39:51
 */
class Version20150416133948 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_favourite 
            DROP CONSTRAINT FK_55DB04521BAD783F
        ");
        $this->addSql("
            ALTER TABLE hevinci_favourite 
            ADD CONSTRAINT FK_55DB04521BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_favourite 
            DROP CONSTRAINT FK_55DB04521BAD783F
        ");
        $this->addSql("
            ALTER TABLE hevinci_favourite 
            ADD CONSTRAINT FK_55DB04521BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id)
        ");
    }
}