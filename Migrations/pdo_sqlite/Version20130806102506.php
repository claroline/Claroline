<?php

namespace Claroline\AnnouncementBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/06 10:25:07
 */
class Version20130806102506 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_announcement (
                id INTEGER NOT NULL, 
                creator_id INTEGER NOT NULL, 
                aggregate_id INTEGER NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                content VARCHAR(1023) DEFAULT NULL, 
                announcer VARCHAR(255) DEFAULT NULL, 
                publication_date DATETIME DEFAULT NULL, 
                visible BOOLEAN NOT NULL, 
                visible_from DATETIME DEFAULT NULL, 
                visible_until DATETIME DEFAULT NULL, 
                \"order\" INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
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
            DROP TABLE claro_announcement
        ");
    }
}