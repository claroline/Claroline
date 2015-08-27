<?php

namespace FormaLibre\ReservationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/08/27 10:44:08
 */
class Version20150827104408 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE formalibre_reservation_resource_rights (
                id INT AUTO_INCREMENT NOT NULL, 
                mask INT NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE resourcerights_resource (
                resourcerights_id INT NOT NULL, 
                resource_id INT NOT NULL, 
                INDEX IDX_8FB6B25D31DA61A3 (resourcerights_id), 
                INDEX IDX_8FB6B25D89329D25 (resource_id), 
                PRIMARY KEY(resourcerights_id, resource_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE resourcerights_role (
                resourcerights_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_819B13C231DA61A3 (resourcerights_id), 
                INDEX IDX_819B13C2D60322AC (role_id), 
                PRIMARY KEY(resourcerights_id, role_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE resourcerights_resource 
            ADD CONSTRAINT FK_8FB6B25D31DA61A3 FOREIGN KEY (resourcerights_id) 
            REFERENCES formalibre_reservation_resource_rights (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE resourcerights_resource 
            ADD CONSTRAINT FK_8FB6B25D89329D25 FOREIGN KEY (resource_id) 
            REFERENCES formalibre_reservation_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE resourcerights_role 
            ADD CONSTRAINT FK_819B13C231DA61A3 FOREIGN KEY (resourcerights_id) 
            REFERENCES formalibre_reservation_resource_rights (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE resourcerights_role 
            ADD CONSTRAINT FK_819B13C2D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE resourcerights_resource 
            DROP FOREIGN KEY FK_8FB6B25D31DA61A3
        ");
        $this->addSql("
            ALTER TABLE resourcerights_role 
            DROP FOREIGN KEY FK_819B13C231DA61A3
        ");
        $this->addSql("
            DROP TABLE formalibre_reservation_resource_rights
        ");
        $this->addSql("
            DROP TABLE resourcerights_resource
        ");
        $this->addSql("
            DROP TABLE resourcerights_role
        ");
    }
}