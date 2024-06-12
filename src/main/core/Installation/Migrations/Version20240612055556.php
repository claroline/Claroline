<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/06/12 05:57:00
 */
final class Version20240612055556 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_planned_object 
            DROP FOREIGN KEY FK_5F6CC1D754177093
        ');
        $this->addSql('
            DROP INDEX IDX_5F6CC1D754177093 ON claro_planned_object
        ');
        $this->addSql('
            ALTER TABLE claro_planned_object 
            DROP room_id
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_planned_object 
            ADD room_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_planned_object 
            ADD CONSTRAINT FK_5F6CC1D754177093 FOREIGN KEY (room_id) 
            REFERENCES claro_location_room (id) ON UPDATE NO ACTION 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_5F6CC1D754177093 ON claro_planned_object (room_id)
        ');
    }
}
