<?php

namespace Claroline\AnnouncementBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/13 10:27:38
 */
class Version20130813102738 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_778754E361220EA6
        ");
        $this->addSql("
            DROP INDEX IDX_778754E3D0BBCCBE
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_announcement AS 
            SELECT id, 
            creator_id, 
            aggregate_id, 
            title, 
            content, 
            announcer, 
            creation_date, 
            publication_date, 
            visible, 
            visible_from, 
            visible_until 
            FROM claro_announcement
        ");
        $this->addSql("
            DROP TABLE claro_announcement
        ");
        $this->addSql("
            CREATE TABLE claro_announcement (
                id INTEGER NOT NULL, 
                creator_id INTEGER NOT NULL, 
                aggregate_id INTEGER NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                content VARCHAR(1023) NOT NULL, 
                announcer VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                publication_date DATETIME DEFAULT NULL, 
                visible BOOLEAN NOT NULL, 
                visible_from DATETIME DEFAULT NULL, 
                visible_until DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_778754E361220EA6 FOREIGN KEY (creator_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_778754E3D0BBCCBE FOREIGN KEY (aggregate_id) 
                REFERENCES claro_announcement_aggregate (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_announcement (
                id, creator_id, aggregate_id, title, 
                content, announcer, creation_date, 
                publication_date, visible, visible_from, 
                visible_until
            ) 
            SELECT id, 
            creator_id, 
            aggregate_id, 
            title, 
            content, 
            announcer, 
            creation_date, 
            publication_date, 
            visible, 
            visible_from, 
            visible_until 
            FROM __temp__claro_announcement
        ");
        $this->addSql("
            DROP TABLE __temp__claro_announcement
        ");
        $this->addSql("
            CREATE INDEX IDX_778754E361220EA6 ON claro_announcement (creator_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_778754E3D0BBCCBE ON claro_announcement (aggregate_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_announcement 
            ADD COLUMN announcement_order INTEGER NOT NULL
        ");
    }
}