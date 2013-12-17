<?php

namespace Claroline\ForumBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/12/17 03:30:55
 */
class Version20131217153054 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_forum_category (
                id INT IDENTITY NOT NULL, 
                forum_id INT, 
                created DATETIME2(6) NOT NULL, 
                modificationDate DATETIME2(6) NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_2192ACF729CCBAD0 ON claro_forum_category (forum_id)
        ");
        $this->addSql("
            ALTER TABLE claro_forum_category 
            ADD CONSTRAINT FK_2192ACF729CCBAD0 FOREIGN KEY (forum_id) 
            REFERENCES claro_forum (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            sp_RENAME 'claro_forum_subject.forum_id', 
            'category_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            ADD isSticked BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject ALTER COLUMN category_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            DROP CONSTRAINT FK_273AA20B29CCBAD0
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_273AA20B29CCBAD0'
            ) 
            ALTER TABLE claro_forum_subject 
            DROP CONSTRAINT IDX_273AA20B29CCBAD0 ELSE 
            DROP INDEX IDX_273AA20B29CCBAD0 ON claro_forum_subject
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20B12469DE2 FOREIGN KEY (category_id) 
            REFERENCES claro_forum_category (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_273AA20B12469DE2 ON claro_forum_subject (category_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            DROP CONSTRAINT FK_273AA20B12469DE2
        ");
        $this->addSql("
            DROP TABLE claro_forum_category
        ");
        $this->addSql("
            sp_RENAME 'claro_forum_subject.category_id', 
            'forum_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            DROP COLUMN isSticked
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject ALTER COLUMN forum_id INT
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_273AA20B12469DE2'
            ) 
            ALTER TABLE claro_forum_subject 
            DROP CONSTRAINT IDX_273AA20B12469DE2 ELSE 
            DROP INDEX IDX_273AA20B12469DE2 ON claro_forum_subject
        ");
        $this->addSql("
            ALTER TABLE claro_forum_subject 
            ADD CONSTRAINT FK_273AA20B29CCBAD0 FOREIGN KEY (forum_id) 
            REFERENCES claro_forum (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_273AA20B29CCBAD0 ON claro_forum_subject (forum_id)
        ");
    }
}