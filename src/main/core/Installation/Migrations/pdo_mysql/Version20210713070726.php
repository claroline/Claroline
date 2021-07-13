<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/07/13 07:07:36
 */
class Version20210713070726 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_booking_material 
            ADD location_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_booking_material 
            ADD CONSTRAINT FK_F7ABA7F564D218E FOREIGN KEY (location_id) 
            REFERENCES claro__location (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_F7ABA7F564D218E ON claro_booking_material (location_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_booking_material 
            DROP FOREIGN KEY FK_F7ABA7F564D218E
        ');
        $this->addSql('
            DROP INDEX IDX_F7ABA7F564D218E ON claro_booking_material
        ');
        $this->addSql('
            ALTER TABLE claro_booking_material 
            DROP location_id
        ');
    }
}
