<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/16 01:52:25
 */
class Version20140616135223 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_facet_role (
                facet_id INT NOT NULL, 
                role_id INT NOT NULL, 
                PRIMARY KEY(facet_id, role_id), 
                INDEX IDX_CDD5845DFC889F24 (facet_id), 
                INDEX IDX_CDD5845DD60322AC (role_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_field_facet_role (
                id INT AUTO_INCREMENT NOT NULL, 
                role_id INT NOT NULL, 
                field_id INT NOT NULL, 
                canOpen BOOLEAN NOT NULL, 
                canEdit BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_12F52A52D60322AC (role_id), 
                INDEX IDX_12F52A52443707B0 (field_id)
            )
        ");
        $this->addSql("
            ALTER TABLE claro_facet_role 
            ADD CONSTRAINT FK_CDD5845DFC889F24 FOREIGN KEY (facet_id) 
            REFERENCES claro_facet (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_facet_role 
            ADD CONSTRAINT FK_CDD5845DD60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_role 
            ADD CONSTRAINT FK_12F52A52D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_role 
            ADD CONSTRAINT FK_12F52A52443707B0 FOREIGN KEY (field_id) 
            REFERENCES claro_field_facet (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_value CHANGE stringValue stringValue VARCHAR(255) DEFAULT NULL, 
            CHANGE floatValue floatValue DOUBLE PRECISION DEFAULT NULL, 
            CHANGE dateValue dateValue DATETIME DEFAULT NULL
        ");
        $this->addSql("
            DROP INDEX UNIQ_F6C21DB25E237E06 ON claro_field_facet
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_facet_role
        ");
        $this->addSql("
            DROP TABLE claro_field_facet_role
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_F6C21DB25E237E06 ON claro_field_facet (name)
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_value CHANGE stringValue stringValue VARCHAR(255) NOT NULL, 
            CHANGE floatValue floatValue DOUBLE PRECISION NOT NULL, 
            CHANGE dateValue dateValue DATETIME NOT NULL
        ");
    }
}