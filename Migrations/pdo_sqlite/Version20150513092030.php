<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/05/13 09:20:32
 */
class Version20150513092030 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_pathtemplate AS 
            SELECT id, 
            name, 
            structure 
            FROM innova_pathtemplate
        ");
        $this->addSql("
            DROP TABLE innova_pathtemplate
        ");
        $this->addSql("
            CREATE TABLE innova_pathtemplate (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                structure CLOB NOT NULL, 
                breadcrumbs BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO innova_pathtemplate (id, name, structure) 
            SELECT id, 
            name, 
            structure 
            FROM __temp__innova_pathtemplate
        ");
        $this->addSql("
            DROP TABLE __temp__innova_pathtemplate
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_pathtemplate AS 
            SELECT id, 
            name, 
            structure 
            FROM innova_pathtemplate
        ");
        $this->addSql("
            DROP TABLE innova_pathtemplate
        ");
        $this->addSql("
            CREATE TABLE innova_pathtemplate (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                structure CLOB NOT NULL, 
                description CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO innova_pathtemplate (id, name, structure) 
            SELECT id, 
            name, 
            structure 
            FROM __temp__innova_pathtemplate
        ");
        $this->addSql("
            DROP TABLE __temp__innova_pathtemplate
        ");
    }
}