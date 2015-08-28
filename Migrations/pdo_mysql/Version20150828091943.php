<?php

namespace FormaLibre\ReservationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/08/28 09:19:44
 */
class Version20150828091943 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation 
            DROP FOREIGN KEY FK_71058F70BC91F416
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation 
            ADD CONSTRAINT FK_71058F70BC91F416 FOREIGN KEY (resource) 
            REFERENCES formalibre_reservation_resource (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation 
            DROP FOREIGN KEY FK_71058F70BC91F416
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation 
            ADD CONSTRAINT FK_71058F70BC91F416 FOREIGN KEY (resource) 
            REFERENCES formalibre_reservation_resource (id)
        ");
    }
}