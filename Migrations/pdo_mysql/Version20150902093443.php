<?php

namespace FormaLibre\ReservationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/09/02 09:34:44
 */
class Version20150902093443 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation CHANGE lastmodification last_modification DATETIME NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation CHANGE last_modification lastModification DATETIME NOT NULL
        ");
    }
}