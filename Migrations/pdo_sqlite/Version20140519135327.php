<?php

namespace Icap\DropzoneBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/19 01:53:30
 */
class Version20140519135327 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__dropzonebundle_drop 
            ADD COLUMN unlocked_drop BOOLEAN DEFAULT '0' NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_3AD19BA6A8C6E7BD
        ");
        $this->addSql("
            DROP INDEX IDX_3AD19BA6A76ED395
        ");
        $this->addSql("
            DROP INDEX UNIQ_3AD19BA65342CDF
        ");
        $this->addSql("
            DROP INDEX unique_drop_for_user_in_drop_zone
        ");
        $this->addSql("
            DROP INDEX unique_drop_number_in_drop_zone
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__dropzonebundle_drop AS 
            SELECT id, 
            drop_zone_id, 
            user_id, 
            hidden_directory_id, 
            drop_date, 
            reported, 
            finished, 
            number, 
            auto_closed_drop 
            FROM icap__dropzonebundle_drop
        ");
        $this->addSql("
            DROP TABLE icap__dropzonebundle_drop
        ");
        $this->addSql("
            CREATE TABLE icap__dropzonebundle_drop (
                id INTEGER NOT NULL, 
                drop_zone_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                hidden_directory_id INTEGER DEFAULT NULL, 
                drop_date DATETIME NOT NULL, 
                reported BOOLEAN NOT NULL, 
                finished BOOLEAN NOT NULL, 
                number INTEGER NOT NULL, 
                auto_closed_drop BOOLEAN DEFAULT '0' NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_3AD19BA6A8C6E7BD FOREIGN KEY (drop_zone_id) 
                REFERENCES icap__dropzonebundle_dropzone (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_3AD19BA6A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_3AD19BA65342CDF FOREIGN KEY (hidden_directory_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__dropzonebundle_drop (
                id, drop_zone_id, user_id, hidden_directory_id, 
                drop_date, reported, finished, number, 
                auto_closed_drop
            ) 
            SELECT id, 
            drop_zone_id, 
            user_id, 
            hidden_directory_id, 
            drop_date, 
            reported, 
            finished, 
            number, 
            auto_closed_drop 
            FROM __temp__icap__dropzonebundle_drop
        ");
        $this->addSql("
            DROP TABLE __temp__icap__dropzonebundle_drop
        ");
        $this->addSql("
            CREATE INDEX IDX_3AD19BA6A8C6E7BD ON icap__dropzonebundle_drop (drop_zone_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3AD19BA6A76ED395 ON icap__dropzonebundle_drop (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_3AD19BA65342CDF ON icap__dropzonebundle_drop (hidden_directory_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_drop_for_user_in_drop_zone ON icap__dropzonebundle_drop (drop_zone_id, user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX unique_drop_number_in_drop_zone ON icap__dropzonebundle_drop (drop_zone_id, number)
        ");
    }
}