<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/04 09:52:33
 */
class Version20140304095232 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_EB8D2852F85E0677
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D28525126AC48
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D285282D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_user AS 
            SELECT id, 
            workspace_id, 
            first_name, 
            last_name, 
            username, 
            password, 
            salt, 
            phone, 
            mail, 
            administrative_code, 
            creation_date, 
            reset_password, 
            hash_time, 
            picture, 
            description, 
            locale, 
            hasAcceptedTerms, 
            is_enabled, 
            is_mail_notified, 
            last_uri 
            FROM claro_user
        ");
        $this->addSql("
            DROP TABLE claro_user
        ");
        $this->addSql("
            CREATE TABLE claro_user (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                first_name VARCHAR(50) NOT NULL, 
                last_name VARCHAR(50) NOT NULL, 
                username VARCHAR(255) NOT NULL, 
                password VARCHAR(255) NOT NULL, 
                salt VARCHAR(255) NOT NULL, 
                phone VARCHAR(255) DEFAULT NULL, 
                mail VARCHAR(255) NOT NULL, 
                administrative_code VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                reset_password VARCHAR(255) DEFAULT NULL, 
                hash_time INTEGER DEFAULT NULL, 
                picture VARCHAR(255) DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                locale VARCHAR(255) DEFAULT NULL, 
                hasAcceptedTerms BOOLEAN DEFAULT NULL, 
                is_enabled BOOLEAN NOT NULL, 
                is_mail_notified BOOLEAN NOT NULL, 
                last_uri VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user (
                id, workspace_id, first_name, last_name, 
                username, password, salt, phone, mail, 
                administrative_code, creation_date, 
                reset_password, hash_time, picture, 
                description, locale, hasAcceptedTerms, 
                is_enabled, is_mail_notified, last_uri
            ) 
            SELECT id, 
            workspace_id, 
            first_name, 
            last_name, 
            username, 
            password, 
            salt, 
            phone, 
            mail, 
            administrative_code, 
            creation_date, 
            reset_password, 
            hash_time, 
            picture, 
            description, 
            locale, 
            hasAcceptedTerms, 
            is_enabled, 
            is_mail_notified, 
            last_uri 
            FROM __temp__claro_user
        ");
        $this->addSql("
            DROP TABLE __temp__claro_user
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852F85E0677 ON claro_user (username)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D28525126AC48 ON claro_user (mail)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D285282D40A1F ON claro_user (workspace_id)
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
            root 
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
                lvl, rgt, root
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

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_EB8D2852F85E0677
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D28525126AC48
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D285282D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_user AS 
            SELECT id, 
            workspace_id, 
            first_name, 
            last_name, 
            username, 
            password, 
            locale, 
            salt, 
            phone, 
            mail, 
            administrative_code, 
            creation_date, 
            reset_password, 
            hash_time, 
            picture, 
            description, 
            hasAcceptedTerms, 
            is_enabled, 
            is_mail_notified, 
            last_uri 
            FROM claro_user
        ");
        $this->addSql("
            DROP TABLE claro_user
        ");
        $this->addSql("
            CREATE TABLE claro_user (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                first_name VARCHAR(50) NOT NULL, 
                last_name VARCHAR(50) NOT NULL, 
                username VARCHAR(255) NOT NULL, 
                password VARCHAR(255) NOT NULL, 
                locale VARCHAR(255) DEFAULT NULL, 
                salt VARCHAR(255) NOT NULL, 
                phone VARCHAR(255) DEFAULT NULL, 
                mail VARCHAR(255) NOT NULL, 
                administrative_code VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                reset_password VARCHAR(255) DEFAULT NULL, 
                hash_time INTEGER DEFAULT NULL, 
                picture VARCHAR(255) DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                hasAcceptedTerms BOOLEAN DEFAULT NULL, 
                is_enabled BOOLEAN NOT NULL, 
                is_mail_notified BOOLEAN NOT NULL, 
                last_uri VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user (
                id, workspace_id, first_name, last_name, 
                username, password, locale, salt, 
                phone, mail, administrative_code, 
                creation_date, reset_password, hash_time, 
                picture, description, hasAcceptedTerms, 
                is_enabled, is_mail_notified, last_uri
            ) 
            SELECT id, 
            workspace_id, 
            first_name, 
            last_name, 
            username, 
            password, 
            locale, 
            salt, 
            phone, 
            mail, 
            administrative_code, 
            creation_date, 
            reset_password, 
            hash_time, 
            picture, 
            description, 
            hasAcceptedTerms, 
            is_enabled, 
            is_mail_notified, 
            last_uri 
            FROM __temp__claro_user
        ");
        $this->addSql("
            DROP TABLE __temp__claro_user
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852F85E0677 ON claro_user (username)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D28525126AC48 ON claro_user (mail)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D285282D40A1F ON claro_user (workspace_id)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD COLUMN is_public BOOLEAN NOT NULL
        ");
    }
}