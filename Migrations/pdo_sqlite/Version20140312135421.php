<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/12 01:54:22
 */
class Version20140312135421 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE innova_stepWho 
            ADD COLUMN is_default BOOLEAN NOT NULL DEFAULT 0
        ");
        $this->addSql("
            ALTER TABLE innova_stepWhere 
            ADD COLUMN is_default BOOLEAN NOT NULL DEFAULT 0
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_stepWhere AS 
            SELECT id, 
            name 
            FROM innova_stepWhere
        ");
        $this->addSql("
            DROP TABLE innova_stepWhere
        ");
        $this->addSql("
            CREATE TABLE innova_stepWhere (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO innova_stepWhere (id, name) 
            SELECT id, 
            name 
            FROM __temp__innova_stepWhere
        ");
        $this->addSql("
            DROP TABLE __temp__innova_stepWhere
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__innova_stepWho AS 
            SELECT id, 
            name 
            FROM innova_stepWho
        ");
        $this->addSql("
            DROP TABLE innova_stepWho
        ");
        $this->addSql("
            CREATE TABLE innova_stepWho (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO innova_stepWho (id, name) 
            SELECT id, 
            name 
            FROM __temp__innova_stepWho
        ");
        $this->addSql("
            DROP TABLE __temp__innova_stepWho
        ");
    }
}