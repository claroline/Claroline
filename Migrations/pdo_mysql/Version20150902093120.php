<?php

namespace FormaLibre\ReservationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/09/02 09:31:21
 */
class Version20150902093120 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation 
            ADD lastModification DATETIME NOT NULL, 
            CHANGE comment comment VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation 
            DROP lastModification, 
            CHANGE comment comment LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci
        ");
    }
}