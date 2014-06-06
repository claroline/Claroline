<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/04 03:06:28
 */
class Version20140604150627 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_competence (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                score INTEGER NOT NULL, 
                isPlatform BOOLEAN DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F65DE32582D40A1F ON claro_competence (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_competence_users (
                id INTEGER NOT NULL, 
                competence_id INTEGER DEFAULT NULL, 
                user_id INTEGER NOT NULL, 
                score INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_2E80B8E215761DAB ON claro_competence_users (competence_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2E80B8E2A76ED395 ON claro_competence_users (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX competence_user_unique ON claro_competence_users (competence_id, user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_competence_hierarchy (
                id INTEGER NOT NULL, 
                competence_id INTEGER NOT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                root_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D4A415FD15761DAB ON claro_competence_hierarchy (competence_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D4A415FD727ACA70 ON claro_competence_hierarchy (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D4A415FD79066886 ON claro_competence_hierarchy (root_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX competence_hrch_unique ON claro_competence_hierarchy (
                competence_id, parent_id, root_id
            )
        ");
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
            DROP INDEX IDX_D9028545727ACA70
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_workspace AS 
            SELECT id, 
            parent_id, 
            user_id, 
            name, 
            code, 
            displayable, 
            guid, 
            self_registration, 
            self_unregistration, 
            discr, 
            lft, 
            lvl, 
            rgt, 
            root, 
            creation_date, 
            description 
            FROM claro_workspace
        ");
        $this->addSql("
            DROP TABLE claro_workspace
        ");
        $this->addSql("
            CREATE TABLE claro_workspace (
                id INTEGER NOT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                displayable BOOLEAN NOT NULL, 
                guid VARCHAR(255) NOT NULL, 
                self_registration BOOLEAN NOT NULL, 
                self_unregistration BOOLEAN NOT NULL, 
                discr VARCHAR(255) NOT NULL, 
                lft INTEGER DEFAULT NULL, 
                lvl INTEGER DEFAULT NULL, 
                rgt INTEGER DEFAULT NULL, 
                root INTEGER DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                creation_date INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_D9028545727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D9028545A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_workspace (
                id, parent_id, user_id, name, code, 
                displayable, guid, self_registration, 
                self_unregistration, discr, lft, 
                lvl, rgt, root, creation_date, description
            ) 
            SELECT id, 
            parent_id, 
            user_id, 
            name, 
            code, 
            displayable, 
            guid, 
            self_registration, 
            self_unregistration, 
            discr, 
            lft, 
            lvl, 
            rgt, 
            root, 
            creation_date, 
            description 
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
        $this->addSql("
            CREATE INDEX IDX_D9028545727ACA70 ON claro_workspace (parent_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_competence
        ");
        $this->addSql("
            DROP TABLE claro_competence_users
        ");
        $this->addSql("
            DROP TABLE claro_competence_hierarchy
        ");
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
            DROP INDEX IDX_D9028545727ACA70
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_workspace AS 
            SELECT id, 
            user_id, 
            parent_id, 
            name, 
            description, 
            code, 
            displayable, 
            guid, 
            self_registration, 
            self_unregistration, 
            creation_date, 
            discr, 
            lft, 
            lvl, 
            rgt, 
            root 
            FROM claro_workspace
        ");
        $this->addSql("
            DROP TABLE claro_workspace
        ");
        $this->addSql("
            CREATE TABLE claro_workspace (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                code VARCHAR(255) NOT NULL, 
                displayable BOOLEAN NOT NULL, 
                guid VARCHAR(255) NOT NULL, 
                self_registration BOOLEAN NOT NULL, 
                self_unregistration BOOLEAN NOT NULL, 
                discr VARCHAR(255) NOT NULL, 
                lft INTEGER DEFAULT NULL, 
                lvl INTEGER DEFAULT NULL, 
                rgt INTEGER DEFAULT NULL, 
                root INTEGER DEFAULT NULL, 
                creation_date INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_D9028545A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D9028545727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_workspace (
                id, user_id, parent_id, name, description, 
                code, displayable, guid, self_registration, 
                self_unregistration, creation_date, 
                discr, lft, lvl, rgt, root
            ) 
            SELECT id, 
            user_id, 
            parent_id, 
            name, 
            description, 
            code, 
            displayable, 
            guid, 
            self_registration, 
            self_unregistration, 
            creation_date, 
            discr, 
            lft, 
            lvl, 
            rgt, 
            root 
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
        $this->addSql("
            CREATE INDEX IDX_D9028545727ACA70 ON claro_workspace (parent_id)
        ");
    }
}