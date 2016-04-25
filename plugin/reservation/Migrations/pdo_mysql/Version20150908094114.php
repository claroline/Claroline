<?php

namespace FormaLibre\ReservationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/09/08 09:41:15
 */
class Version20150908094114 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE formalibre_reservation_resource_type (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(50) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE formalibre_reservation_resource_rights (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_id INT NOT NULL, 
                role_id INT NOT NULL, 
                mask INT NOT NULL, 
                INDEX IDX_92EF974689329D25 (resource_id), 
                INDEX IDX_92EF9746D60322AC (role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE formalibre_reservation_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_type INT NOT NULL, 
                name LONGTEXT NOT NULL, 
                max_time_reservation VARCHAR(8) DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                localisation VARCHAR(255) DEFAULT NULL, 
                quantity INT NOT NULL, 
                color VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_7AAF141683FEF793 (resource_type), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE formalibre_reservation (
                id INT AUTO_INCREMENT NOT NULL, 
                resource INT NOT NULL, 
                event_id INT NOT NULL, 
                comment VARCHAR(255) DEFAULT NULL, 
                last_modification DATETIME NOT NULL, 
                INDEX IDX_71058F70BC91F416 (resource), 
                UNIQUE INDEX UNIQ_71058F7071F7E88B (event_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation_resource_rights 
            ADD CONSTRAINT FK_92EF974689329D25 FOREIGN KEY (resource_id) 
            REFERENCES formalibre_reservation_resource (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation_resource_rights 
            ADD CONSTRAINT FK_92EF9746D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation_resource 
            ADD CONSTRAINT FK_7AAF141683FEF793 FOREIGN KEY (resource_type) 
            REFERENCES formalibre_reservation_resource_type (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation 
            ADD CONSTRAINT FK_71058F70BC91F416 FOREIGN KEY (resource) 
            REFERENCES formalibre_reservation_resource (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation 
            ADD CONSTRAINT FK_71058F7071F7E88B FOREIGN KEY (event_id) 
            REFERENCES claro_event (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE formalibre_reservation_resource 
            DROP FOREIGN KEY FK_7AAF141683FEF793
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation_resource_rights 
            DROP FOREIGN KEY FK_92EF974689329D25
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation 
            DROP FOREIGN KEY FK_71058F70BC91F416
        ');
        $this->addSql('
            DROP TABLE formalibre_reservation_resource_type
        ');
        $this->addSql('
            DROP TABLE formalibre_reservation_resource_rights
        ');
        $this->addSql('
            DROP TABLE formalibre_reservation_resource
        ');
        $this->addSql('
            DROP TABLE formalibre_reservation
        ');
    }
}
