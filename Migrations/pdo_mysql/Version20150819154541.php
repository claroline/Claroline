<?php

namespace FormaLibre\ReservationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/08/19 03:45:41
 */
class Version20150819154541 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource_type 
            DROP description, 
            DROP localisation
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource 
            ADD description LONGTEXT NOT NULL, 
            ADD localisation VARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource 
            DROP description, 
            DROP localisation
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource_type 
            ADD description LONGTEXT NOT NULL COLLATE utf8_unicode_ci, 
            ADD localisation VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ");
    }
}