<?php

namespace FormaLibre\ReservationBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/02/02 02:28:30
 */
class Version20180202142829 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE formalibre_reservation_resource_organizations (
                resource_id INT NOT NULL, 
                organization_id INT NOT NULL, 
                INDEX IDX_110DC6D989329D25 (resource_id), 
                INDEX IDX_110DC6D932C8A3DE (organization_id), 
                PRIMARY KEY(resource_id, organization_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation_resource_organizations 
            ADD CONSTRAINT FK_110DC6D989329D25 FOREIGN KEY (resource_id) 
            REFERENCES formalibre_reservation_resource (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation_resource_organizations 
            ADD CONSTRAINT FK_110DC6D932C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation_resource_type 
            ADD uuid VARCHAR(36) NOT NULL, 
            CHANGE name name VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            UPDATE formalibre_reservation_resource_type SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_E6D40EF9D17F50A6 ON formalibre_reservation_resource_type (uuid)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX reservation_unique_resource_type ON formalibre_reservation_resource_type (name)
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation_resource 
            ADD uuid VARCHAR(36) NOT NULL, 
            CHANGE name name VARCHAR(255) NOT NULL, 
            CHANGE max_time_reservation max_time_reservation VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            UPDATE formalibre_reservation_resource SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_7AAF1416D17F50A6 ON formalibre_reservation_resource (uuid)
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation_resource_rights 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE formalibre_reservation_resource_rights SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_92EF9746D17F50A6 ON formalibre_reservation_resource_rights (uuid)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX reservation_unique_resource_rights ON formalibre_reservation_resource_rights (role_id, resource_id)
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE formalibre_reservation SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_71058F70D17F50A6 ON formalibre_reservation (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE formalibre_reservation_resource_organizations
        ');
        $this->addSql('
            DROP INDEX UNIQ_71058F70D17F50A6 ON formalibre_reservation
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_7AAF1416D17F50A6 ON formalibre_reservation_resource
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation_resource 
            DROP uuid, 
            CHANGE name name LONGTEXT NOT NULL COLLATE utf8_unicode_ci, 
            CHANGE max_time_reservation max_time_reservation VARCHAR(8) DEFAULT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            DROP INDEX UNIQ_92EF9746D17F50A6 ON formalibre_reservation_resource_rights
        ');
        $this->addSql('
            DROP INDEX reservation_unique_resource_rights ON formalibre_reservation_resource_rights
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation_resource_rights 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_E6D40EF9D17F50A6 ON formalibre_reservation_resource_type
        ');
        $this->addSql('
            DROP INDEX reservation_unique_resource_type ON formalibre_reservation_resource_type
        ');
        $this->addSql('
            ALTER TABLE formalibre_reservation_resource_type 
            DROP uuid, 
            CHANGE name name VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci
        ');
    }
}
