<?php

namespace Claroline\AnnouncementBundle\Migrations\sqlsrv;

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
                id INT IDENTITY NOT NULL, 
                creator_id INT NOT NULL, 
                aggregate_id INT NOT NULL, 
                title NVARCHAR(255), 
                content NVARCHAR(1023), 
                announcer NVARCHAR(255), 
                publication_date DATETIME2(6), 
                visible BIT NOT NULL, 
                visible_from DATETIME2(6), 
                visible_until DATETIME2(6), 
                [order] INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_778754E361220EA6 ON claro_announcement (creator_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_778754E3D0BBCCBE ON claro_announcement (aggregate_id)
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
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_announcement
        ");
    }
}