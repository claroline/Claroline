<?php

namespace Claroline\AnnouncementBundle\Migrations\pdo_mysql;

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
                id INT AUTO_INCREMENT NOT NULL, 
                creator_id INT NOT NULL, 
                aggregate_id INT NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                content VARCHAR(1023) DEFAULT NULL, 
                announcer VARCHAR(255) DEFAULT NULL, 
                publication_date DATETIME DEFAULT NULL, 
                visible TINYINT(1) NOT NULL, 
                visible_from DATETIME DEFAULT NULL, 
                visible_until DATETIME DEFAULT NULL, 
                `order` INT NOT NULL, 
                INDEX IDX_778754E361220EA6 (creator_id), 
                INDEX IDX_778754E3D0BBCCBE (aggregate_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
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