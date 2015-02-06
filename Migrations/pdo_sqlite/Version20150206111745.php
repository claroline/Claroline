<?php

namespace Claroline\CursusBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/06 11:17:46
 */
class Version20150206111745 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_cursusbundle_course (
                id INTEGER NOT NULL, 
                code VARCHAR(255) NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                public_registration BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_3359D34977153098 ON claro_cursusbundle_course (code)
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus (
                id INTEGER NOT NULL, 
                course_id INTEGER DEFAULT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                code VARCHAR(255) DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                description CLOB DEFAULT NULL, 
                blocking BOOLEAN NOT NULL, 
                details CLOB DEFAULT NULL, 
                cursus_order INTEGER NOT NULL, 
                root INTEGER DEFAULT NULL, 
                lvl INTEGER NOT NULL, 
                lft INTEGER NOT NULL, 
                rgt INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_27921C3377153098 ON claro_cursusbundle_cursus (code)
        ");
        $this->addSql("
            CREATE INDEX IDX_27921C33591CC992 ON claro_cursusbundle_cursus (course_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_27921C33727ACA70 ON claro_cursusbundle_cursus (parent_id)
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus_displayed_word (
                id INTEGER NOT NULL, 
                word VARCHAR(255) NOT NULL, 
                displayed_name VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_14E7B098C3F17511 ON claro_cursusbundle_cursus_displayed_word (word)
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus_group (
                id INTEGER NOT NULL, 
                group_id INTEGER NOT NULL, 
                cursus_id INTEGER NOT NULL, 
                registration_date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_EA4DDE93FE54D947 ON claro_cursusbundle_cursus_group (group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EA4DDE9340AEF4B9 ON claro_cursusbundle_cursus_group (cursus_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_group_unique_cursus_group ON claro_cursusbundle_cursus_group (cursus_id, group_id)
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus_user (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                cursus_id INTEGER NOT NULL, 
                registration_date DATETIME NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_8AA52D8A76ED395 ON claro_cursusbundle_cursus_user (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8AA52D840AEF4B9 ON claro_cursusbundle_cursus_user (cursus_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX cursus_user_unique_cursus_user ON claro_cursusbundle_cursus_user (cursus_id, user_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_cursusbundle_course
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_cursus
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_cursus_displayed_word
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_cursus_group
        ");
        $this->addSql("
            DROP TABLE claro_cursusbundle_cursus_user
        ");
    }
}