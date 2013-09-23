<?php

namespace Icap\LessonBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/23 03:53:10
 */
class Version20130923155308 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__lesson_chapter (
                id INT IDENTITY NOT NULL, 
                lesson_id INT, 
                parent_id INT, 
                title NVARCHAR(255), 
                text VARCHAR(MAX), 
                lft INT NOT NULL, 
                lvl INT NOT NULL, 
                rgt INT NOT NULL, 
                root INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_3D7E3C8CCDF80196 ON icap__lesson_chapter (lesson_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3D7E3C8C727ACA70 ON icap__lesson_chapter (parent_id)
        ");
        $this->addSql("
            CREATE TABLE icap__lesson (
                id INT IDENTITY NOT NULL, 
                root_id INT, 
                resourceNode_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D9B3613079066886 ON icap__lesson (root_id) 
            WHERE root_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D9B36130B87FAB32 ON icap__lesson (resourceNode_id) 
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__lesson_chapter 
            ADD CONSTRAINT FK_3D7E3C8CCDF80196 FOREIGN KEY (lesson_id) 
            REFERENCES icap__lesson (id)
        ");
        $this->addSql("
            ALTER TABLE icap__lesson_chapter 
            ADD CONSTRAINT FK_3D7E3C8C727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES icap__lesson_chapter (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__lesson 
            ADD CONSTRAINT FK_D9B3613079066886 FOREIGN KEY (root_id) 
            REFERENCES icap__lesson_chapter (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__lesson 
            ADD CONSTRAINT FK_D9B36130B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__lesson_chapter 
            DROP CONSTRAINT FK_3D7E3C8C727ACA70
        ");
        $this->addSql("
            ALTER TABLE icap__lesson 
            DROP CONSTRAINT FK_D9B3613079066886
        ");
        $this->addSql("
            ALTER TABLE icap__lesson_chapter 
            DROP CONSTRAINT FK_3D7E3C8CCDF80196
        ");
        $this->addSql("
            DROP TABLE icap__lesson_chapter
        ");
        $this->addSql("
            DROP TABLE icap__lesson
        ");
    }
}