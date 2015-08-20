<?php

namespace FormaLibre\ReservationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/08/20 11:48:58
 */
class Version20150820114857 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource CHANGE max_time_reservation max_time_reservation INT DEFAULT NULL, 
            CHANGE description description LONGTEXT DEFAULT NULL, 
            CHANGE localisation localisation VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource CHANGE max_time_reservation max_time_reservation INT NOT NULL, 
            CHANGE description description LONGTEXT NOT NULL COLLATE utf8_unicode_ci, 
            CHANGE localisation localisation VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ");
    }
}