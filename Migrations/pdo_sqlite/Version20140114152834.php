<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/14 03:28:35
 */
class Version20140114152834 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_pathtemplate AS 
            SELECT id, 
            name, 
            description, 
            step 
            FROM innova_pathtemplate
        ");
        $this->addSql("
            DROP TABLE innova_pathtemplate
        ");
        $this->addSql("
            CREATE TABLE innova_pathtemplate (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                structure CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO innova_pathtemplate (id, name, description, structure) 
            SELECT id, 
            name, 
            description, 
            step 
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
            description, 
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
                description CLOB DEFAULT NULL, 
                step CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO innova_pathtemplate (id, name, description, step) 
            SELECT id, 
            name, 
            description, 
            structure 
            FROM __temp__innova_pathtemplate
        ");
        $this->addSql("
            DROP TABLE __temp__innova_pathtemplate
        ");
    }
}