<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/08 03:50:35
 */
class Version20130808155035 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_EA81C80BE1F029B6
        ");
        $this->addSql("
            DROP INDEX UNIQ_EA81C80BB87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_file AS 
            SELECT id, 
            size, 
            hash_name, 
            resourceNode_id 
            FROM claro_file
        ");
        $this->addSql("
            DROP TABLE claro_file
        ");
        $this->addSql("
            CREATE TABLE claro_file (
                id INTEGER NOT NULL, 
                size INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                hash_name VARCHAR(50) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EA81C80BB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_file (
                id, size, hash_name, resourceNode_id
            ) 
            SELECT id, 
            size, 
            hash_name, 
            resourceNode_id 
            FROM __temp__claro_file
        ");
        $this->addSql("
            DROP TABLE __temp__claro_file
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EA81C80BE1F029B6 ON claro_file (hash_name)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EA81C80BB87FAB32 ON claro_file (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_EA81C80BE1F029B6
        ");
        $this->addSql("
            DROP INDEX UNIQ_EA81C80BB87FAB32
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_file AS 
            SELECT id, 
            size, 
            hash_name, 
            resourceNode_id 
            FROM claro_file
        ");
        $this->addSql("
            DROP TABLE claro_file
        ");
        $this->addSql("
            CREATE TABLE claro_file (
                id INTEGER NOT NULL, 
                size INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                hash_name VARCHAR(36) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EA81C80BB87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_file (
                id, size, hash_name, resourceNode_id
            ) 
            SELECT id, 
            size, 
            hash_name, 
            resourceNode_id 
            FROM __temp__claro_file
        ");
        $this->addSql("
            DROP TABLE __temp__claro_file
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EA81C80BE1F029B6 ON claro_file (hash_name)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EA81C80BB87FAB32 ON claro_file (resourceNode_id)
        ");
    }
}