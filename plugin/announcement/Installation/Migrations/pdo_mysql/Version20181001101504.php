<?php

namespace Claroline\AnnouncementBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 08:11:14
 */
class Version20181001101504 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_announcement_aggregate (
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_79BF2C8CD17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_79BF2C8CB87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_announcement (
                id INT AUTO_INCREMENT NOT NULL, 
                creator_id INT NOT NULL, 
                aggregate_id INT NOT NULL, 
                task_id INT DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                content LONGTEXT NOT NULL, 
                announcer VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                publication_date DATETIME DEFAULT NULL, 
                visible TINYINT(1) NOT NULL, 
                visible_from DATETIME DEFAULT NULL, 
                visible_until DATETIME DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_778754E3D17F50A6 (uuid), 
                INDEX IDX_778754E361220EA6 (creator_id), 
                INDEX IDX_778754E3D0BBCCBE (aggregate_id), 
                INDEX IDX_778754E38DB60186 (task_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_announcement_roles (
                announcement_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_4075322B913AEA17 (announcement_id), 
                INDEX IDX_4075322BD60322AC (role_id), 
                PRIMARY KEY(announcement_id, role_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_announcements_send (
                id INT AUTO_INCREMENT NOT NULL, 
                announcement_id INT DEFAULT NULL, 
                data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_7C739377D17F50A6 (uuid), 
                INDEX IDX_7C739377913AEA17 (announcement_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
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
        $this->addSql('
            ALTER TABLE claro_announcement 
            ADD CONSTRAINT FK_778754E38DB60186 FOREIGN KEY (task_id) 
            REFERENCES claro_scheduled_task (id)
        ');
        $this->addSql('
            ALTER TABLE claro_announcement_roles 
            ADD CONSTRAINT FK_4075322B913AEA17 FOREIGN KEY (announcement_id) 
            REFERENCES claro_announcement (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_announcement_roles 
            ADD CONSTRAINT FK_4075322BD60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_announcements_send 
            ADD CONSTRAINT FK_7C739377913AEA17 FOREIGN KEY (announcement_id) 
            REFERENCES claro_announcement (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_announcement 
            DROP FOREIGN KEY FK_778754E3D0BBCCBE
        ');
        $this->addSql('
            ALTER TABLE claro_announcement_roles 
            DROP FOREIGN KEY FK_4075322B913AEA17
        ');
        $this->addSql('
            ALTER TABLE claro_announcements_send 
            DROP FOREIGN KEY FK_7C739377913AEA17
        ');
        $this->addSql('
            DROP TABLE claro_announcement_aggregate
        ');
        $this->addSql('
            DROP TABLE claro_announcement
        ');
        $this->addSql('
            DROP TABLE claro_announcement_roles
        ');
        $this->addSql('
            DROP TABLE claro_announcements_send
        ');
    }
}
