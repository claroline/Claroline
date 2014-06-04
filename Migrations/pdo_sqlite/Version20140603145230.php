<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/03 02:52:33
 */
class Version20140603145230 extends AbstractMigration
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
            DROP INDEX UNIQ_EB8D2852F85E0677
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D28525126AC48
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D285282D40A1F
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D2852181F3A64
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
            last_uri, 
            is_mail_displayed 
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
                is_mail_displayed BOOLEAN NOT NULL, 
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
                is_enabled, is_mail_notified, last_uri, 
                is_mail_displayed
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
            last_uri, 
            is_mail_displayed 
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
            DROP INDEX UNIQ_A76799FFAA23F6C8
        ");
        $this->addSql("
            DROP INDEX UNIQ_A76799FF2DE62210
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF460F904B
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF98EC6B7B
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF61220EA6
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF54B9D732
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_node AS 
            SELECT id, 
            previous_id, 
            license_id, 
            icon_id, 
            creator_id, 
            parent_id, 
            workspace_id, 
            resource_type_id, 
            next_id, 
            creation_date, 
            modification_date, 
            name, 
            lvl, 
            path, 
            mime_type, 
            class 
            FROM claro_resource_node
        ");
        $this->addSql("
            DROP TABLE claro_resource_node
        ");
        $this->addSql("
            CREATE TABLE claro_resource_node (
                id INTEGER NOT NULL, 
                previous_id INTEGER DEFAULT NULL, 
                license_id INTEGER DEFAULT NULL, 
                icon_id INTEGER DEFAULT NULL, 
                creator_id INTEGER NOT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER NOT NULL, 
                resource_type_id INTEGER NOT NULL, 
                next_id INTEGER DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                modification_date DATETIME NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                lvl INTEGER DEFAULT NULL, 
                path VARCHAR(3000) DEFAULT NULL, 
                mime_type VARCHAR(255) DEFAULT NULL, 
                class VARCHAR(256) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_A76799FF2DE62210 FOREIGN KEY (previous_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF460F904B FOREIGN KEY (license_id) 
                REFERENCES claro_license (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF54B9D732 FOREIGN KEY (icon_id) 
                REFERENCES claro_resource_icon (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF61220EA6 FOREIGN KEY (creator_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF98EC6B7B FOREIGN KEY (resource_type_id) 
                REFERENCES claro_resource_type (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FFAA23F6C8 FOREIGN KEY (next_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_node (
                id, previous_id, license_id, icon_id, 
                creator_id, parent_id, workspace_id, 
                resource_type_id, next_id, creation_date, 
                modification_date, name, lvl, path, 
                mime_type, class
            ) 
            SELECT id, 
            previous_id, 
            license_id, 
            icon_id, 
            creator_id, 
            parent_id, 
            workspace_id, 
            resource_type_id, 
            next_id, 
            creation_date, 
            modification_date, 
            name, 
            lvl, 
            path, 
            mime_type, 
            class 
            FROM __temp__claro_resource_node
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_node
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A76799FFAA23F6C8 ON claro_resource_node (next_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A76799FF2DE62210 ON claro_resource_node (previous_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF460F904B ON claro_resource_node (license_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF98EC6B7B ON claro_resource_node (resource_type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF61220EA6 ON claro_resource_node (creator_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF54B9D732 ON claro_resource_node (icon_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF727ACA70 ON claro_resource_node (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF82D40A1F ON claro_resource_node (workspace_id)
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
        $this->addSql("
            DROP INDEX user_badge_unique
        ");
        $this->addSql("
            DROP INDEX IDX_7EBB381FA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_7EBB381FF7A2C2FC
        ");
        $this->addSql("
            DROP INDEX IDX_7EBB381FBB9D6FEE
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_user_badge AS 
            SELECT id, 
            user_id, 
            issuer_id, 
            badge_id, 
            issued_at, 
            expired_at 
            FROM claro_user_badge
        ");
        $this->addSql("
            DROP TABLE claro_user_badge
        ");
        $this->addSql("
            CREATE TABLE claro_user_badge (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                issuer_id INTEGER DEFAULT NULL, 
                badge_id INTEGER NOT NULL, 
                issued_at DATETIME NOT NULL, 
                expired_at DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_7EBB381FA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_7EBB381FBB9D6FEE FOREIGN KEY (issuer_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_7EBB381FF7A2C2FC FOREIGN KEY (badge_id) 
                REFERENCES claro_badge (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user_badge (
                id, user_id, issuer_id, badge_id, issued_at, 
                expired_at
            ) 
            SELECT id, 
            user_id, 
            issuer_id, 
            badge_id, 
            issued_at, 
            expired_at 
            FROM __temp__claro_user_badge
        ");
        $this->addSql("
            DROP TABLE __temp__claro_user_badge
        ");
        $this->addSql("
            CREATE UNIQUE INDEX user_badge_unique ON claro_user_badge (user_id, badge_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_7EBB381FA76ED395 ON claro_user_badge (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_7EBB381FF7A2C2FC ON claro_user_badge (badge_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_7EBB381FBB9D6FEE ON claro_user_badge (issuer_id)
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
            DROP INDEX IDX_A76799FF460F904B
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF98EC6B7B
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF61220EA6
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF54B9D732
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_A76799FF82D40A1F
        ");
        $this->addSql("
            DROP INDEX UNIQ_A76799FFAA23F6C8
        ");
        $this->addSql("
            DROP INDEX UNIQ_A76799FF2DE62210
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_resource_node AS 
            SELECT id, 
            license_id, 
            resource_type_id, 
            creator_id, 
            icon_id, 
            parent_id, 
            workspace_id, 
            next_id, 
            previous_id, 
            creation_date, 
            modification_date, 
            name, 
            lvl, 
            path, 
            mime_type, 
            class 
            FROM claro_resource_node
        ");
        $this->addSql("
            DROP TABLE claro_resource_node
        ");
        $this->addSql("
            CREATE TABLE claro_resource_node (
                id INTEGER NOT NULL, 
                license_id INTEGER DEFAULT NULL, 
                resource_type_id INTEGER NOT NULL, 
                creator_id INTEGER NOT NULL, 
                icon_id INTEGER DEFAULT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER NOT NULL, 
                next_id INTEGER DEFAULT NULL, 
                previous_id INTEGER DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                modification_date DATETIME NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                lvl INTEGER DEFAULT NULL, 
                path VARCHAR(3000) DEFAULT NULL, 
                mime_type VARCHAR(255) DEFAULT NULL, 
                class VARCHAR(256) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_A76799FF460F904B FOREIGN KEY (license_id) 
                REFERENCES claro_license (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF98EC6B7B FOREIGN KEY (resource_type_id) 
                REFERENCES claro_resource_type (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF61220EA6 FOREIGN KEY (creator_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF54B9D732 FOREIGN KEY (icon_id) 
                REFERENCES claro_resource_icon (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF727ACA70 FOREIGN KEY (parent_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FFAA23F6C8 FOREIGN KEY (next_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_A76799FF2DE62210 FOREIGN KEY (previous_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_resource_node (
                id, license_id, resource_type_id, 
                creator_id, icon_id, parent_id, workspace_id, 
                next_id, previous_id, creation_date, 
                modification_date, name, lvl, path, 
                mime_type, class
            ) 
            SELECT id, 
            license_id, 
            resource_type_id, 
            creator_id, 
            icon_id, 
            parent_id, 
            workspace_id, 
            next_id, 
            previous_id, 
            creation_date, 
            modification_date, 
            name, 
            lvl, 
            path, 
            mime_type, 
            class 
            FROM __temp__claro_resource_node
        ");
        $this->addSql("
            DROP TABLE __temp__claro_resource_node
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF460F904B ON claro_resource_node (license_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF98EC6B7B ON claro_resource_node (resource_type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF61220EA6 ON claro_resource_node (creator_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF54B9D732 ON claro_resource_node (icon_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF727ACA70 ON claro_resource_node (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF82D40A1F ON claro_resource_node (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A76799FFAA23F6C8 ON claro_resource_node (next_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A76799FF2DE62210 ON claro_resource_node (previous_id)
        ");
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
            last_uri, 
            is_mail_displayed 
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
                is_mail_displayed BOOLEAN NOT NULL, 
                public_url VARCHAR(255) DEFAULT NULL, 
                has_tuned_public_url BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user (
                id, workspace_id, first_name, last_name, 
                username, password, locale, salt, 
                phone, mail, administrative_code, 
                creation_date, reset_password, hash_time, 
                picture, description, hasAcceptedTerms, 
                is_enabled, is_mail_notified, last_uri, 
                is_mail_displayed
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
            last_uri, 
            is_mail_displayed 
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
            CREATE UNIQUE INDEX UNIQ_EB8D2852181F3A64 ON claro_user (public_url)
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge 
            ADD COLUMN comment CLOB DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD COLUMN creation_date INTEGER NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD COLUMN description CLOB DEFAULT NULL
        ");
    }
}