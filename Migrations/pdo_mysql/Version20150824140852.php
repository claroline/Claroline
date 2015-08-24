<?php

namespace FormaLibre\ReservationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/08/24 02:08:52
 */
class Version20150824140852 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation 
            DROP duration, 
            DROP last_update
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation 
            ADD duration INT NOT NULL, 
            ADD last_update INT NOT NULL
        ");
    }
}