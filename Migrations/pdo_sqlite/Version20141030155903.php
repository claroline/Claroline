<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/30 03:59:05
 */
class Version20141030155903 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_type 
            ADD COLUMN publish BOOLEAN NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_type AS 
            SELECT id, 
            name, 
            max_content_page 
            FROM claro_type
        ");
        $this->addSql("
            DROP TABLE claro_type
        ");
        $this->addSql("
            CREATE TABLE claro_type (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                max_content_page INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            INSERT INTO claro_type (id, name, max_content_page) 
            SELECT id, 
            name, 
            max_content_page 
            FROM __temp__claro_type
        ");
        $this->addSql("
            DROP TABLE __temp__claro_type
        ");
    }
}