<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/04/06 05:40:28
 */
class Version20210406054027 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_planned_object (
                id INT AUTO_INCREMENT NOT NULL, 
                location_id INT DEFAULT NULL, 
                creator_id INT DEFAULT NULL, 
                event_type VARCHAR(255) NOT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                color VARCHAR(255) DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_5F6CC1D7D17F50A6 (uuid), 
                INDEX IDX_5F6CC1D764D218E (location_id), 
                INDEX IDX_5F6CC1D761220EA6 (creator_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_planning (
                id INT AUTO_INCREMENT NOT NULL, 
                objectId VARCHAR(255) NOT NULL, 
                objectClass VARCHAR(255) NOT NULL,
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_9C4BCA00D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_planning_planned_object (
                planning_id INT NOT NULL, 
                planned_object_id INT NOT NULL, 
                INDEX IDX_A05487943D865311 (planning_id), 
                INDEX IDX_A0548794A669922F (planned_object_id), 
                PRIMARY KEY(planning_id, planned_object_id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_planned_object 
            ADD CONSTRAINT FK_5F6CC1D764D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_planned_object 
            ADD CONSTRAINT FK_5F6CC1D761220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_planning_planned_object 
            ADD CONSTRAINT FK_A05487943D865311 FOREIGN KEY (planning_id) 
            REFERENCES claro_planning (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_planning_planned_object 
            ADD CONSTRAINT FK_A0548794A669922F FOREIGN KEY (planned_object_id) 
            REFERENCES claro_planned_object (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_planning_planned_object 
            DROP FOREIGN KEY FK_A0548794A669922F
        ');
        $this->addSql('
            ALTER TABLE claro_planning_planned_object 
            DROP FOREIGN KEY FK_A05487943D865311
        ');
        $this->addSql('
            DROP TABLE claro_planned_object
        ');
        $this->addSql('
            DROP TABLE claro_planning
        ');
        $this->addSql('
            DROP TABLE claro_planning_planned_object
        ');
    }
}
