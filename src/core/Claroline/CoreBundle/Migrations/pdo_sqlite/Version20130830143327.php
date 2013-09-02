<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/08/30 02:33:28
 */
class Version20130830143327 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_resource_action 
            ADD COLUMN is_form BOOLEAN NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_7EE4A91298EC6B7B
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_action AS 
            SELECT id, 
            resource_type_id, 
            name, 
            async, 
            is_custom, 
            permRequired 
            FROM claro_resource_action
        ");
        $this->addSql("
            DROP TABLE claro_resource_action
        ");
        $this->addSql("
            CREATE TABLE claro_resource_action (
                id INTEGER NOT NULL, 
                resource_type_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                async BOOLEAN DEFAULT NULL, 
                is_custom BOOLEAN NOT NULL, 
                permRequired VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_7EE4A91298EC6B7B FOREIGN KEY (resource_type_id) 
                REFERENCES claro_resource_type (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_action (
                id, resource_type_id, name, async, 
                is_custom, permRequired
            ) 
            SELECT id, 
            resource_type_id, 
            name, 
            async, 
            is_custom, 
            permRequired 
            FROM __temp__claro_resource_action
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_action
        ");
        $this->addSql("
            CREATE INDEX IDX_7EE4A91298EC6B7B ON claro_resource_action (resource_type_id)
        ");
    }
}