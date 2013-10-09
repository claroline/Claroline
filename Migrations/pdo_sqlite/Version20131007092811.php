<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/07 09:28:12
 */
class Version20131007092811 extends AbstractMigration
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
                step CLOB NOT NULL, 
                description CLOB DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO innova_pathtemplate (id, name, description, step) 
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
                step CLOB NOT NULL, 
                description CLOB NOT NULL, 
                user VARCHAR(255) NOT NULL, 
                edit_date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO innova_pathtemplate (id, name, description, step) 
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
}