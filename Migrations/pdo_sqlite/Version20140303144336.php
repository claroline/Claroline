<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/03 02:43:38
 */
class Version20140303144336 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_badge_collection (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                is_shared BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_BB3FD2DDA76ED395 ON claro_badge_collection (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX slug_idx ON claro_badge_collection (slug)
        ");
        $this->addSql("
            CREATE TABLE claro_badge_collection_badges (
                badgecollection_id INTEGER NOT NULL, 
                badge_id INTEGER NOT NULL, 
                PRIMARY KEY(badgecollection_id, badge_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_FD258D74134B8A11 ON claro_badge_collection_badges (badgecollection_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FD258D74F7A2C2FC ON claro_badge_collection_badges (badge_id)
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
            ALTER TABLE claro_user_badge 
            ADD COLUMN expired_at DATETIME DEFAULT NULL
        ");
        $this->addSql("
            DROP INDEX IDX_74F39F0F82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_badge AS 
            SELECT id, 
            workspace_id, 
            version, 
            image, 
            automatic_award 
            FROM claro_badge
        ");
        $this->addSql("
            DROP TABLE claro_badge
        ");
        $this->addSql("
            CREATE TABLE claro_badge (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                version INTEGER NOT NULL, 
                image VARCHAR(255) NOT NULL, 
                automatic_award BOOLEAN DEFAULT NULL, 
                is_expiring BOOLEAN DEFAULT '0' NOT NULL, 
                expire_duration INTEGER DEFAULT NULL, 
                expire_period INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_74F39F0F82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_badge (
                id, workspace_id, version, image, automatic_award
            ) 
            SELECT id, 
            workspace_id, 
            version, 
            image, 
            automatic_award 
            FROM __temp__claro_badge
        ");
        $this->addSql("
            DROP TABLE __temp__claro_badge
        ");
        $this->addSql("
            CREATE INDEX IDX_74F39F0F82D40A1F ON claro_badge (workspace_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_badge_collection
        ");
        $this->addSql("
            DROP TABLE claro_badge_collection_badges
        ");
        $this->addSql("
            DROP INDEX IDX_74F39F0F82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_badge AS 
            SELECT id, 
            workspace_id, 
            version, 
            automatic_award, 
            image 
            FROM claro_badge
        ");
        $this->addSql("
            DROP TABLE claro_badge
        ");
        $this->addSql("
            CREATE TABLE claro_badge (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                version INTEGER NOT NULL, 
                automatic_award BOOLEAN DEFAULT NULL, 
                image VARCHAR(255) NOT NULL, 
                expired_at DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_74F39F0F82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_badge (
                id, workspace_id, version, automatic_award, 
                image
            ) 
            SELECT id, 
            workspace_id, 
            version, 
            automatic_award, 
            image 
            FROM __temp__claro_badge
        ");
        $this->addSql("
            DROP TABLE __temp__claro_badge
        ");
        $this->addSql("
            CREATE INDEX IDX_74F39F0F82D40A1F ON claro_badge (workspace_id)
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
            DROP INDEX IDX_7EBB381FA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_7EBB381FF7A2C2FC
        ");
        $this->addSql("
            DROP INDEX IDX_7EBB381FBB9D6FEE
        ");
        $this->addSql("
            DROP INDEX user_badge_unique
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_user_badge AS 
            SELECT id, 
            user_id, 
            badge_id, 
            issuer_id, 
            issued_at 
            FROM claro_user_badge
        ");
        $this->addSql("
            DROP TABLE claro_user_badge
        ");
        $this->addSql("
            CREATE TABLE claro_user_badge (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                badge_id INTEGER NOT NULL, 
                issuer_id INTEGER DEFAULT NULL, 
                issued_at DATETIME NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_7EBB381FA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_7EBB381FF7A2C2FC FOREIGN KEY (badge_id) 
                REFERENCES claro_badge (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_7EBB381FBB9D6FEE FOREIGN KEY (issuer_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user_badge (
                id, user_id, badge_id, issuer_id, issued_at
            ) 
            SELECT id, 
            user_id, 
            badge_id, 
            issuer_id, 
            issued_at 
            FROM __temp__claro_user_badge
        ");
        $this->addSql("
            DROP TABLE __temp__claro_user_badge
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
        $this->addSql("
            CREATE UNIQUE INDEX user_badge_unique ON claro_user_badge (user_id, badge_id)
        ");
    }
}