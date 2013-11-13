<?php

namespace Icap\LessonBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/11/13 03:48:08
 */
class Version20131113154806 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__lesson_chapter 
            ADD slug NVARCHAR(128)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_3D7E3C8C989D9B62 ON icap__lesson_chapter (slug) 
            WHERE slug IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__lesson_chapter 
            DROP COLUMN slug
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_3D7E3C8C989D9B62'
            ) 
            ALTER TABLE icap__lesson_chapter 
            DROP CONSTRAINT UNIQ_3D7E3C8C989D9B62 ELSE 
            DROP INDEX UNIQ_3D7E3C8C989D9B62 ON icap__lesson_chapter
        ");
    }
}