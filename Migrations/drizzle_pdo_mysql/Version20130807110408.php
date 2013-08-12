<?php

namespace Claroline\AnnouncementBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/07 11:04:09
 */
class Version20130807110408 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_announcement (
                id INT AUTO_INCREMENT NOT NULL, 
                creator_id INT NOT NULL, 
                aggregate_id INT NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                content VARCHAR(1023) DEFAULT NULL, 
                announcer VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                publication_date DATETIME DEFAULT NULL, 
                visible BOOLEAN NOT NULL, 
                visible_from DATETIME DEFAULT NULL, 
                visible_until DATETIME DEFAULT NULL, 
                announcement_order INT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_778754E361220EA6 (creator_id), 
                INDEX IDX_778754E3D0BBCCBE (aggregate_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_announcement_aggregate (
                id INT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            ALTER TABLE claro_announcement 
            ADD CONSTRAINT FK_778754E361220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_announcement 
            ADD CONSTRAINT FK_778754E3D0BBCCBE FOREIGN KEY (aggregate_id) 
            REFERENCES claro_announcement_aggregate (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_announcement_aggregate 
            ADD CONSTRAINT FK_79BF2C8CBF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_announcement 
            DROP FOREIGN KEY FK_778754E3D0BBCCBE
        ");
        $this->addSql("
            DROP TABLE claro_announcement
        ");
        $this->addSql("
            DROP TABLE claro_announcement_aggregate
        ");
    }
}