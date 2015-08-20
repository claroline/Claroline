<?php

namespace FormaLibre\ReservationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/08/19 01:39:46
 */
class Version20150819133946 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE formalibre_reservation_resource_type (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description LONGTEXT NOT NULL, 
                localisation VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_reservation_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_type INT NOT NULL, 
                name LONGTEXT NOT NULL, 
                max_time_reservation INT NOT NULL, 
                INDEX IDX_7AAF141683FEF793 (resource_type), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_reservation (
                id INT AUTO_INCREMENT NOT NULL, 
                resource INT NOT NULL, 
                duration INT NOT NULL, 
                last_update INT NOT NULL, 
                INDEX IDX_71058F70BC91F416 (resource), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE reservation_event (
                reservation_id INT NOT NULL, 
                event_id INT NOT NULL, 
                INDEX IDX_78D1DA00B83297E7 (reservation_id), 
                INDEX IDX_78D1DA0071F7E88B (event_id), 
                PRIMARY KEY(reservation_id, event_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource 
            ADD CONSTRAINT FK_7AAF141683FEF793 FOREIGN KEY (resource_type) 
            REFERENCES formalibre_reservation_resource_type (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation 
            ADD CONSTRAINT FK_71058F70BC91F416 FOREIGN KEY (resource) 
            REFERENCES formalibre_reservation_resource (id)
        ");
        $this->addSql("
            ALTER TABLE reservation_event 
            ADD CONSTRAINT FK_78D1DA00B83297E7 FOREIGN KEY (reservation_id) 
            REFERENCES formalibre_reservation (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE reservation_event 
            ADD CONSTRAINT FK_78D1DA0071F7E88B FOREIGN KEY (event_id) 
            REFERENCES claro_event (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource 
            DROP FOREIGN KEY FK_7AAF141683FEF793
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation 
            DROP FOREIGN KEY FK_71058F70BC91F416
        ");
        $this->addSql("
            ALTER TABLE reservation_event 
            DROP FOREIGN KEY FK_78D1DA00B83297E7
        ");
        $this->addSql("
            DROP TABLE formalibre_reservation_resource_type
        ");
        $this->addSql("
            DROP TABLE formalibre_reservation_resource
        ");
        $this->addSql("
            DROP TABLE formalibre_reservation
        ");
        $this->addSql("
            DROP TABLE reservation_event
        ");
    }
}