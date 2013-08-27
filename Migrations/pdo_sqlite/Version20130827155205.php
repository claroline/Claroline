<?php

namespace Innova\PathBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/27 03:52:06
 */
class Version20130827155205 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE innova_pathtemplate (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description CLOB NOT NULL, 
                step CLOB NOT NULL, 
                user VARCHAR(255) NOT NULL, 
                edit_date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE innova_path (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                path CLOB NOT NULL, 
                user VARCHAR(255) NOT NULL, 
                edit_date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_CE19F05482D40A1F ON innova_path (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE innova_pathtemplate
        ");
        $this->addSql("
            DROP TABLE innova_path
        ");
    }
}