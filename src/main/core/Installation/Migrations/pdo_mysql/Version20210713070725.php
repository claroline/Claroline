<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Claroline\MigrationBundle\Migrations\ConditionalMigrationTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/06/14 10:10:42
 */
class Version20210713070725 extends AbstractMigration
{
    use ConditionalMigrationTrait;

    public function up(Schema $schema): void
    {
        if ($this->checkTableExists('claro_booking_room', $this->connection) && !$this->checkTableExists('claro_location_room', $this->connection)) {
            $this->addSql('
                RENAME TABLE claro_booking_room TO claro_location_room
            ');

            $this->addSql('
                ALTER TABLE claro_location_room 
                DROP FOREIGN KEY FK_5030FE2A64D218E
            ');
            $this->addSql('
                DROP INDEX uniq_5030fe2a77153098 ON claro_location_room
            ');
            $this->addSql('
                CREATE UNIQUE INDEX UNIQ_DFA335DB77153098 ON claro_location_room (code)
            ');
            $this->addSql('
                DROP INDEX uniq_5030fe2ad17f50a6 ON claro_location_room
            ');
            $this->addSql('
                CREATE UNIQUE INDEX UNIQ_DFA335DBD17F50A6 ON claro_location_room (uuid)
            ');
            $this->addSql('
                DROP INDEX idx_5030fe2a64d218e ON claro_location_room
            ');
            $this->addSql('
                CREATE INDEX IDX_DFA335DB64D218E ON claro_location_room (location_id)
            ');
            $this->addSql('
                ALTER TABLE claro_location_room 
                ADD CONSTRAINT FK_5030FE2A64D218E FOREIGN KEY (location_id) 
                REFERENCES claro__location (id) 
                ON DELETE SET NULL
            ');
            $this->addSql('
                ALTER TABLE claro_planned_object 
                ADD room_id INT DEFAULT NULL, 
                ADD locationUrl VARCHAR(255) DEFAULT NULL
            ');
            $this->addSql('
                ALTER TABLE claro_planned_object 
                ADD CONSTRAINT FK_5F6CC1D754177093 FOREIGN KEY (room_id) 
                REFERENCES claro_location_room (id) 
                ON DELETE SET NULL
            ');
            $this->addSql('
                CREATE INDEX IDX_5F6CC1D754177093 ON claro_planned_object (room_id)
            ');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_location_room 
            DROP FOREIGN KEY FK_DFA335DB64D218E
        ');
        $this->addSql('
            DROP INDEX uniq_dfa335dbd17f50a6 ON claro_location_room
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_5030FE2AD17F50A6 ON claro_location_room (uuid)
        ');
        $this->addSql('
            DROP INDEX uniq_dfa335db77153098 ON claro_location_room
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_5030FE2A77153098 ON claro_location_room (code)
        ');
        $this->addSql('
            DROP INDEX idx_dfa335db64d218e ON claro_location_room
        ');
        $this->addSql('
            CREATE INDEX IDX_5030FE2A64D218E ON claro_location_room (location_id)
        ');
        $this->addSql('
            ALTER TABLE claro_location_room 
            ADD CONSTRAINT FK_DFA335DB64D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_planned_object 
            DROP FOREIGN KEY FK_5F6CC1D754177093
        ');
        $this->addSql('
            DROP INDEX IDX_5F6CC1D754177093 ON claro_planned_object
        ');
        $this->addSql('
            ALTER TABLE claro_planned_object 
            DROP room_id, 
            DROP locationUrl
        ');
    }
}
