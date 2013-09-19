<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/19 01:01:32
 */
class Version20130919130132 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_stepType (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_stepWhere (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_stepWho (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE innova_stepType
        ");
        $this->addSql("
            DROP TABLE innova_stepWhere
        ");
        $this->addSql("
            DROP TABLE innova_stepWho
        ");
    }
}