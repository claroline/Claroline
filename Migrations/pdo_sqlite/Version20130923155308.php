<?php

namespace Icap\LessonBundle\Migrations\pdo_sqlite;

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
                id INTEGER NOT NULL, 
                lesson_id INTEGER DEFAULT NULL, 
                parent_id INTEGER DEFAULT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                text CLOB DEFAULT NULL, 
                lft INTEGER NOT NULL, 
                lvl INTEGER NOT NULL, 
                rgt INTEGER NOT NULL, 
                root INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
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
                id INTEGER NOT NULL, 
                root_id INTEGER DEFAULT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D9B3613079066886 ON icap__lesson (root_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D9B36130B87FAB32 ON icap__lesson (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__lesson_chapter
        ");
        $this->addSql("
            DROP TABLE icap__lesson
        ");
    }
}