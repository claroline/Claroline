<?php

namespace HeVinci\FavouriteBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/04/01 02:34:05
 */
class Version20150401143404 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE hevinci_favourite (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                resource_node_id INT DEFAULT NULL, 
                INDEX IDX_55DB0452A76ED395 (user_id), 
                INDEX IDX_55DB04521BAD783F (resource_node_id), 
                UNIQUE INDEX UNIQ_55DB0452A76ED3951BAD783F (user_id, resource_node_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE hevinci_favourite 
            ADD CONSTRAINT FK_55DB0452A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE hevinci_favourite 
            ADD CONSTRAINT FK_55DB04521BAD783F FOREIGN KEY (resource_node_id) 
            REFERENCES claro_resource_node (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE hevinci_favourite
        ');
    }
}
