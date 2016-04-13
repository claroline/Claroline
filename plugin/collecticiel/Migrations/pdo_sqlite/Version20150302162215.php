<?php

namespace Innova\CollecticielBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/03/02 04:22:21
 */
class Version20150302162215 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_collecticielbundle_document 
            ADD COLUMN validate BOOLEAN NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX IDX_1C357F0C1BAD783F
        ');
        $this->addSql('
            DROP INDEX IDX_1C357F0C4D224760
        ');
        $this->addSql('
            CREATE TEMPORARY TABLE __temp__innova_collecticielbundle_document AS 
            SELECT id, 
            resource_node_id, 
            drop_id, 
            type, 
            url 
            FROM innova_collecticielbundle_document
        ');
        $this->addSql('
            DROP TABLE innova_collecticielbundle_document
        ');
        $this->addSql('
            CREATE TABLE innova_collecticielbundle_document (
                id INTEGER NOT NULL, 
                resource_node_id INTEGER DEFAULT NULL, 
                drop_id INTEGER NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                url VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1C357F0C1BAD783F FOREIGN KEY (resource_node_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_1C357F0C4D224760 FOREIGN KEY (drop_id) 
                REFERENCES innova_collecticielbundle_drop (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ');
        $this->addSql('
            INSERT INTO innova_collecticielbundle_document (
                id, resource_node_id, drop_id, type, 
                url
            ) 
            SELECT id, 
            resource_node_id, 
            drop_id, 
            type, 
            url 
            FROM __temp__innova_collecticielbundle_document
        ');
        $this->addSql('
            DROP TABLE __temp__innova_collecticielbundle_document
        ');
        $this->addSql('
            CREATE INDEX IDX_1C357F0C1BAD783F ON innova_collecticielbundle_document (resource_node_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_1C357F0C4D224760 ON innova_collecticielbundle_document (drop_id)
        ');
    }
}
