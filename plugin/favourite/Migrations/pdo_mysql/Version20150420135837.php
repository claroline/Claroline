<?php

namespace HeVinci\FavouriteBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/04/20 01:58:39
 */
class Version20150420135837 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE hevinci_favourite 
            DROP FOREIGN KEY FK_55DB04521BAD783F
        ');
        $this->addSql('
            ALTER TABLE hevinci_favourite 
            ADD CONSTRAINT FK_55DB04521BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE hevinci_favourite 
            DROP FOREIGN KEY FK_55DB04521BAD783F
        ');
        $this->addSql('
            ALTER TABLE hevinci_favourite 
            ADD CONSTRAINT FK_55DB04521BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id)
        ');
    }
}
