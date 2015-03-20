<?php

namespace UJM\ExoBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/20 02:18:10
 */
class Version20150320141808 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_2F6069779D5B92F9
        ");
        $this->addSql("
            DROP INDEX IDX_2F606977A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_2F60697712469DE2
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__ujm_question AS 
            SELECT id, 
            category_id, 
            expertise_id, 
            user_id, 
            title, 
            description, 
            date_create, 
            date_modify, 
            locked, 
            model 
            FROM ujm_question
        ");
        $this->addSql("
            DROP TABLE ujm_question
        ");
        $this->addSql("
            CREATE TABLE ujm_question (
                id INTEGER NOT NULL, 
                category_id INTEGER DEFAULT NULL, 
                expertise_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                description CLOB DEFAULT NULL COLLATE utf8_unicode_ci, 
                date_create DATETIME NOT NULL, 
                date_modify DATETIME DEFAULT NULL, 
                locked BOOLEAN DEFAULT NULL, 
                model BOOLEAN DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_2F60697712469DE2 FOREIGN KEY (category_id) 
                REFERENCES ujm_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_2F6069779D5B92F9 FOREIGN KEY (expertise_id) 
                REFERENCES ujm_expertise (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_2F606977A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO ujm_question (
                id, category_id, expertise_id, user_id, 
                title, description, date_create, 
                date_modify, locked, model
            ) 
            SELECT id, 
            category_id, 
            expertise_id, 
            user_id, 
            title, 
            description, 
            date_create, 
            date_modify, 
            locked, 
            model 
            FROM __temp__ujm_question
        ");
        $this->addSql("
            DROP TABLE __temp__ujm_question
        ");
        $this->addSql("
            CREATE INDEX IDX_2F6069779D5B92F9 ON ujm_question (expertise_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2F606977A76ED395 ON ujm_question (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2F60697712469DE2 ON ujm_question (category_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_2F6069779D5B92F9
        ");
        $this->addSql("
            DROP INDEX IDX_2F606977A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_2F60697712469DE2
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__ujm_question AS 
            SELECT id, 
            expertise_id, 
            user_id, 
            category_id, 
            title, 
            description, 
            date_create, 
            date_modify, 
            locked, 
            model 
            FROM ujm_question
        ");
        $this->addSql("
            DROP TABLE ujm_question
        ");
        $this->addSql("
            CREATE TABLE ujm_question (
                id INTEGER NOT NULL, 
                expertise_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                category_id INTEGER DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                date_create DATETIME NOT NULL, 
                date_modify DATETIME DEFAULT NULL, 
                locked BOOLEAN DEFAULT NULL, 
                model BOOLEAN DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_2F6069779D5B92F9 FOREIGN KEY (expertise_id) 
                REFERENCES ujm_expertise (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_2F606977A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_2F60697712469DE2 FOREIGN KEY (category_id) 
                REFERENCES ujm_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO ujm_question (
                id, expertise_id, user_id, category_id, 
                title, description, date_create, 
                date_modify, locked, model
            ) 
            SELECT id, 
            expertise_id, 
            user_id, 
            category_id, 
            title, 
            description, 
            date_create, 
            date_modify, 
            locked, 
            model 
            FROM __temp__ujm_question
        ");
        $this->addSql("
            DROP TABLE __temp__ujm_question
        ");
        $this->addSql("
            CREATE INDEX IDX_2F6069779D5B92F9 ON ujm_question (expertise_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2F606977A76ED395 ON ujm_question (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2F60697712469DE2 ON ujm_question (category_id)
        ");
    }
}