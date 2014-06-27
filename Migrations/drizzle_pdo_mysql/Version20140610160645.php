<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/10 04:06:47
 */
class Version20140610160645 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_field_facet_value (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                stringValue VARCHAR(255) NOT NULL, 
                floatValue DOUBLE PRECISION NOT NULL, 
                dateValue DATETIME NOT NULL, 
                fieldFacet_id INT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_35307C0AA76ED395 (user_id), 
                INDEX IDX_35307C0A9F9239AF (fieldFacet_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_facet (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                `position` INT NOT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_DCBA6D3A5E237E06 (name)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_field_facet (
                id INT AUTO_INCREMENT NOT NULL, 
                facet_id INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                type INT NOT NULL, 
                `position` INT NOT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_F6C21DB25E237E06 (name), 
                INDEX IDX_F6C21DB2FC889F24 (facet_id)
            )
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_value 
            ADD CONSTRAINT FK_35307C0AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_value 
            ADD CONSTRAINT FK_35307C0A9F9239AF FOREIGN KEY (fieldFacet_id) 
            REFERENCES claro_field_facet (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet 
            ADD CONSTRAINT FK_F6C21DB2FC889F24 FOREIGN KEY (facet_id) 
            REFERENCES claro_facet (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_field_facet 
            DROP FOREIGN KEY FK_F6C21DB2FC889F24
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_value 
            DROP FOREIGN KEY FK_35307C0A9F9239AF
        ");
        $this->addSql("
            DROP TABLE claro_field_facet_value
        ");
        $this->addSql("
            DROP TABLE claro_facet
        ");
        $this->addSql("
            DROP TABLE claro_field_facet
        ");
    }
}