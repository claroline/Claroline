<?php

namespace FormaLibre\ReservationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/09/01 10:42:34
 */
class Version20150901104234 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource 
            ADD color VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation 
            ADD comment LONGTEXT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation 
            DROP comment
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource 
            DROP color
        ");
    }
}