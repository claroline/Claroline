<?php

namespace FormaLibre\ReservationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/08/20 02:43:30
 */
class Version20150820144330 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource 
            DROP FOREIGN KEY FK_7AAF141683FEF793
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource 
            ADD CONSTRAINT FK_7AAF141683FEF793 FOREIGN KEY (resource_type) 
            REFERENCES formalibre_reservation_resource_type (id) 
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
            ALTER TABLE formalibre_reservation_resource 
            ADD CONSTRAINT FK_7AAF141683FEF793 FOREIGN KEY (resource_type) 
            REFERENCES formalibre_reservation_resource_type (id)
        ");
    }
}