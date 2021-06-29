<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Claroline\MigrationBundle\Migrations\ConditionalMigrationTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/12/11 07:29:19
 */
class Version20210713070724 extends AbstractMigration
{
    use ConditionalMigrationTrait;

    public function up(Schema $schema): void
    {
        if (!$this->checkTableExists('claro_booking_material', $this->connection)) {
            $this->addSql('
                CREATE TABLE claro_booking_material (
                    id INT AUTO_INCREMENT NOT NULL, 
                    event_name VARCHAR(255) NOT NULL, 
                    capacity INT NOT NULL, 
                    code VARCHAR(255) NOT NULL, 
                    description LONGTEXT DEFAULT NULL, 
                    poster VARCHAR(255) DEFAULT NULL, 
                    thumbnail VARCHAR(255) DEFAULT NULL, 
                    uuid VARCHAR(36) NOT NULL, 
                    UNIQUE INDEX UNIQ_F7ABA7F577153098 (code), 
                    UNIQUE INDEX UNIQ_F7ABA7F5D17F50A6 (uuid), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
            ');
            $this->addSql('
                CREATE TABLE claro_booking_material_booking (
                    id INT AUTO_INCREMENT NOT NULL, 
                    material_id INT NOT NULL, 
                    start_date DATETIME NOT NULL, 
                    end_date DATETIME NOT NULL, 
                    description LONGTEXT DEFAULT NULL, 
                    uuid VARCHAR(36) NOT NULL, 
                    UNIQUE INDEX UNIQ_280C960DD17F50A6 (uuid), 
                    INDEX IDX_280C960DE308AC6F (material_id), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
            ');
        }

        if (!$this->checkTableExists('claro_booking_room', $this->connection) && !$this->checkTableExists('claro_location_room', $this->connection)) {
            $this->addSql('
                CREATE TABLE claro_booking_room (
                    id INT AUTO_INCREMENT NOT NULL, 
                    location_id INT DEFAULT NULL, 
                    event_name VARCHAR(255) NOT NULL, 
                    capacity INT NOT NULL, 
                    code VARCHAR(255) NOT NULL, 
                    description LONGTEXT DEFAULT NULL, 
                    poster VARCHAR(255) DEFAULT NULL, 
                    thumbnail VARCHAR(255) DEFAULT NULL, 
                    uuid VARCHAR(36) NOT NULL, 
                    UNIQUE INDEX UNIQ_5030FE2A77153098 (code), 
                    UNIQUE INDEX UNIQ_5030FE2AD17F50A6 (uuid), 
                    INDEX IDX_5030FE2A64D218E (location_id), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
            ');
            $this->addSql('
                CREATE TABLE claro_booking_room_booking (
                    id INT AUTO_INCREMENT NOT NULL, 
                    room_id INT NOT NULL, 
                    start_date DATETIME NOT NULL, 
                    end_date DATETIME NOT NULL, 
                    description LONGTEXT DEFAULT NULL, 
                    uuid VARCHAR(36) NOT NULL, 
                    UNIQUE INDEX UNIQ_F4DAFBDFD17F50A6 (uuid), 
                    INDEX IDX_F4DAFBDF54177093 (room_id), 
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
            ');
            $this->addSql('
                ALTER TABLE claro_booking_material_booking 
                ADD CONSTRAINT FK_280C960DE308AC6F FOREIGN KEY (material_id) 
                REFERENCES claro_booking_material (id) 
                ON DELETE CASCADE
            ');
            $this->addSql('
                ALTER TABLE claro_booking_room 
                ADD CONSTRAINT FK_5030FE2A64D218E FOREIGN KEY (location_id) 
                REFERENCES claro__location (id) 
                ON DELETE SET NULL
            ');
            $this->addSql('
                ALTER TABLE claro_booking_room_booking 
                ADD CONSTRAINT FK_F4DAFBDF54177093 FOREIGN KEY (room_id) 
                REFERENCES claro_booking_room (id) 
                ON DELETE CASCADE
            ');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_booking_material_booking 
            DROP FOREIGN KEY FK_280C960DE308AC6F
        ');
        $this->addSql('
            ALTER TABLE claro_booking_room_booking 
            DROP FOREIGN KEY FK_F4DAFBDF54177093
        ');
        $this->addSql('
            DROP TABLE claro_booking_material
        ');
        $this->addSql('
            DROP TABLE claro_booking_material_booking
        ');
        $this->addSql('
            DROP TABLE claro_booking_room
        ');
        $this->addSql('
            DROP TABLE claro_booking_room_booking
        ');
    }
}
