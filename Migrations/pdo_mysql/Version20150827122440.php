<?php

namespace FormaLibre\ReservationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/08/27 12:24:40
 */
class Version20150827122440 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource_rights 
            ADD resource_id INT NOT NULL, 
            ADD role_id INT NOT NULL
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
        $this->addSql("
            CREATE INDEX IDX_92EF974689329D25 ON formalibre_reservation_resource_rights (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_92EF9746D60322AC ON formalibre_reservation_resource_rights (role_id)
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
            DROP INDEX IDX_92EF974689329D25 ON formalibre_reservation_resource_rights
        ");
        $this->addSql("
            DROP INDEX IDX_92EF9746D60322AC ON formalibre_reservation_resource_rights
        ");
        $this->addSql("
            ALTER TABLE formalibre_reservation_resource_rights 
            DROP resource_id, 
            DROP role_id
        ");
    }
}