<?php

namespace Claroline\CursusBundle\Migrations\pdo_sqlsrv;

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
                id INT IDENTITY NOT NULL, 
                code NVARCHAR(255) NOT NULL, 
                title NVARCHAR(255) NOT NULL, 
                description VARCHAR(MAX), 
                public_registration BIT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_3359D34977153098 ON claro_cursusbundle_course (code) 
            WHERE code IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus (
                id INT IDENTITY NOT NULL, 
                course_id INT, 
                parent_id INT, 
                code NVARCHAR(255), 
                title NVARCHAR(255) NOT NULL, 
                description VARCHAR(MAX), 
                blocking BIT NOT NULL, 
                details VARCHAR(MAX), 
                cursus_order INT NOT NULL, 
                root INT, 
                lvl INT NOT NULL, 
                lft INT NOT NULL, 
                rgt INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_27921C3377153098 ON claro_cursusbundle_cursus (code) 
            WHERE code IS NOT NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_27921C33591CC992 ON claro_cursusbundle_cursus (course_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_27921C33727ACA70 ON claro_cursusbundle_cursus (parent_id)
        ");
        $this->addSql("
            EXEC sp_addextendedproperty N 'MS_Description', 
            N '(DC2Type:json_array)', 
            N 'SCHEMA', 
            dbo, 
            N 'TABLE', 
            claro_cursusbundle_cursus, 
            N 'COLUMN', 
            details
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus_displayed_word (
                id INT IDENTITY NOT NULL, 
                word NVARCHAR(255) NOT NULL, 
                displayed_name NVARCHAR(255), 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_14E7B098C3F17511 ON claro_cursusbundle_cursus_displayed_word (word) 
            WHERE word IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus_group (
                id INT IDENTITY NOT NULL, 
                group_id INT NOT NULL, 
                cursus_id INT NOT NULL, 
                registration_date DATETIME2(6) NOT NULL, 
                PRIMARY KEY (id)
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
            WHERE cursus_id IS NOT NULL 
            AND group_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_cursusbundle_cursus_user (
                id INT IDENTITY NOT NULL, 
                user_id INT NOT NULL, 
                cursus_id INT NOT NULL, 
                registration_date DATETIME2(6) NOT NULL, 
                PRIMARY KEY (id)
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
            WHERE cursus_id IS NOT NULL 
            AND user_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus 
            ADD CONSTRAINT FK_27921C33591CC992 FOREIGN KEY (course_id) 
            REFERENCES claro_cursusbundle_course (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus 
            ADD CONSTRAINT FK_27921C33727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_cursusbundle_cursus (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus_group 
            ADD CONSTRAINT FK_EA4DDE93FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus_group 
            ADD CONSTRAINT FK_EA4DDE9340AEF4B9 FOREIGN KEY (cursus_id) 
            REFERENCES claro_cursusbundle_cursus (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus_user 
            ADD CONSTRAINT FK_8AA52D8A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus_user 
            ADD CONSTRAINT FK_8AA52D840AEF4B9 FOREIGN KEY (cursus_id) 
            REFERENCES claro_cursusbundle_cursus (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus 
            DROP CONSTRAINT FK_27921C33591CC992
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus 
            DROP CONSTRAINT FK_27921C33727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus_group 
            DROP CONSTRAINT FK_EA4DDE9340AEF4B9
        ");
        $this->addSql("
            ALTER TABLE claro_cursusbundle_cursus_user 
            DROP CONSTRAINT FK_8AA52D840AEF4B9
        ");
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