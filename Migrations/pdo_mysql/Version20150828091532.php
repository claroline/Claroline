<?php

namespace FormaLibre\ReservationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/08/28 09:15:33
 */
class Version20150828091532 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource_rights 
            DROP FOREIGN KEY FK_92EF974689329D25
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource_rights 
            DROP FOREIGN KEY FK_92EF9746D60322AC
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource_rights 
            ADD CONSTRAINT FK_92EF974689329D25 FOREIGN KEY (resource_id) 
            REFERENCES formalibre_reservation_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource_rights 
            ADD CONSTRAINT FK_92EF9746D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource_rights 
            DROP FOREIGN KEY FK_92EF974689329D25
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource_rights 
            DROP FOREIGN KEY FK_92EF9746D60322AC
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource_rights 
            ADD CONSTRAINT FK_92EF974689329D25 FOREIGN KEY (resource_id) 
            REFERENCES formalibre_reservation_resource (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource_rights 
            ADD CONSTRAINT FK_92EF9746D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id)
        ");
    }
}