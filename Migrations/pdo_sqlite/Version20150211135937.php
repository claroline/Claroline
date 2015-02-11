<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/11 01:59:39
 */
class Version20150211135937 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD COLUMN start_date DATETIME DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD COLUMN end_date DATETIME DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD COLUMN accessible_date BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD COLUMN workspace_type INTEGER DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_D902854577153098
        ");
        $this->addSql("
            DROP INDEX UNIQ_D90285452B6FCFB2
        ");
        $this->addSql("
            DROP INDEX IDX_D9028545A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_workspace AS 
            SELECT id, 
            user_id, 
            name, 
            description, 
            code, 
            maxStorageSize, 
            maxUploadResources, 
            displayable, 
            guid, 
            self_registration, 
            registration_validation, 
            self_unregistration, 
            creation_date, 
            is_personal 
            FROM claro_workspace
        ");
        $this->addSql("
            DROP TABLE claro_workspace
        ");
        $this->addSql("
            CREATE TABLE claro_workspace (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                code VARCHAR(255) NOT NULL, 
                maxStorageSize VARCHAR(255) NOT NULL, 
                maxUploadResources INTEGER NOT NULL, 
                displayable BOOLEAN NOT NULL, 
                guid VARCHAR(255) NOT NULL, 
                self_registration BOOLEAN NOT NULL, 
                registration_validation BOOLEAN NOT NULL, 
                self_unregistration BOOLEAN NOT NULL, 
                creation_date INTEGER DEFAULT NULL, 
                is_personal BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_D9028545A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_workspace (
                id, user_id, name, description, code, 
                maxStorageSize, maxUploadResources, 
                displayable, guid, self_registration, 
                registration_validation, self_unregistration, 
                creation_date, is_personal
            ) 
            SELECT id, 
            user_id, 
            name, 
            description, 
            code, 
            maxStorageSize, 
            maxUploadResources, 
            displayable, 
            guid, 
            self_registration, 
            registration_validation, 
            self_unregistration, 
            creation_date, 
            is_personal 
            FROM __temp__claro_workspace
        ");
        $this->addSql("
            DROP TABLE __temp__claro_workspace
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D902854577153098 ON claro_workspace (code)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D90285452B6FCFB2 ON claro_workspace (guid)
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545A76ED395 ON claro_workspace (user_id)
        ");
    }
}