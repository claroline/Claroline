<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/03 01:22:17
 */
class Version20130903132216 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_1F57E52B98EC6B7B
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_menu_action AS 
            SELECT id, 
            resource_type_id, 
            name, 
            async, 
            is_custom, 
            is_form, 
            permRequired 
            FROM claro_menu_action
        ");
        $this->addSql("
            DROP TABLE claro_menu_action
        ");
        $this->addSql("
            CREATE TABLE claro_menu_action (
                id INTEGER NOT NULL, 
                resource_type_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                async BOOLEAN DEFAULT NULL, 
                is_custom BOOLEAN NOT NULL, 
                is_form BOOLEAN NOT NULL, 
                value VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1F57E52B98EC6B7B FOREIGN KEY (resource_type_id) 
                REFERENCES claro_resource_type (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_menu_action (
                id, resource_type_id, name, async, 
                is_custom, is_form, value
            ) 
            SELECT id, 
            resource_type_id, 
            name, 
            async, 
            is_custom, 
            is_form, 
            permRequired 
            FROM __temp__claro_menu_action
        ");
        $this->addSql("
            DROP TABLE __temp__claro_menu_action
        ");
        $this->addSql("
            CREATE INDEX IDX_1F57E52B98EC6B7B ON claro_menu_action (resource_type_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_1F57E52B98EC6B7B
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_menu_action AS 
            SELECT id, 
            resource_type_id, 
            name, 
            async, 
            is_custom, 
            is_form, 
            value 
            FROM claro_menu_action
        ");
        $this->addSql("
            DROP TABLE claro_menu_action
        ");
        $this->addSql("
            CREATE TABLE claro_menu_action (
                id INTEGER NOT NULL, 
                resource_type_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                async BOOLEAN DEFAULT NULL, 
                is_custom BOOLEAN NOT NULL, 
                is_form BOOLEAN NOT NULL, 
                permRequired VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_1F57E52B98EC6B7B FOREIGN KEY (resource_type_id) 
                REFERENCES claro_resource_type (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_menu_action (
                id, resource_type_id, name, async, 
                is_custom, is_form, permRequired
            ) 
            SELECT id, 
            resource_type_id, 
            name, 
            async, 
            is_custom, 
            is_form, 
            value 
            FROM __temp__claro_menu_action
        ");
        $this->addSql("
            DROP TABLE __temp__claro_menu_action
        ");
        $this->addSql("
            CREATE INDEX IDX_1F57E52B98EC6B7B ON claro_menu_action (resource_type_id)
        ");
    }
}