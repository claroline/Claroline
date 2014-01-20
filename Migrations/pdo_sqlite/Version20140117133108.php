<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/17 01:31:10
 */
class Version20140117133108 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_content 
            ADD COLUMN type VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_content AS 
            SELECT id, 
            title, 
            content, 
            created, 
            modified 
            FROM claro_content
        ");
        $this->addSql("
            DROP TABLE claro_content
        ");
        $this->addSql("
            CREATE TABLE claro_content (
                id INTEGER NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                content CLOB DEFAULT NULL, 
                created DATETIME NOT NULL, 
                modified DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO claro_content (
                id, title, content, created, modified
            ) 
            SELECT id, 
            title, 
            content, 
            created, 
            modified 
            FROM __temp__claro_content
        ");
        $this->addSql("
            DROP TABLE __temp__claro_content
        ");
    }
}