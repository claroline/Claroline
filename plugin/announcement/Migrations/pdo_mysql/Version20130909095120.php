<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/09/09 09:51:21
 */
class Version20130909095120 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_announcement_aggregate (
                id INT AUTO_INCREMENT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_79BF2C8CB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_announcement (
                id INT AUTO_INCREMENT NOT NULL, 
                creator_id INT NOT NULL, 
                aggregate_id INT NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                content VARCHAR(1023) NOT NULL, 
                announcer VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                publication_date DATETIME DEFAULT NULL, 
                visible TINYINT(1) NOT NULL, 
                visible_from DATETIME DEFAULT NULL, 
                visible_until DATETIME DEFAULT NULL, 
                INDEX IDX_778754E361220EA6 (creator_id), 
                INDEX IDX_778754E3D0BBCCBE (aggregate_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_announcement_aggregate 
            ADD CONSTRAINT FK_79BF2C8CB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_announcement 
            ADD CONSTRAINT FK_778754E361220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_announcement 
            ADD CONSTRAINT FK_778754E3D0BBCCBE FOREIGN KEY (aggregate_id) 
            REFERENCES claro_announcement_aggregate (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_announcement 
            DROP FOREIGN KEY FK_778754E3D0BBCCBE
        ');
        $this->addSql('
            DROP TABLE claro_announcement_aggregate
        ');
        $this->addSql('
            DROP TABLE claro_announcement
        ');
    }
}
