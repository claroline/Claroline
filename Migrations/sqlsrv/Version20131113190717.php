<?php

namespace Icap\LessonBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/11/13 07:07:18
 */
class Version20131113190717 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__lesson_chapter ALTER COLUMN title NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__lesson_chapter ALTER COLUMN slug NVARCHAR(128) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__lesson_chapter ALTER COLUMN title NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE icap__lesson_chapter ALTER COLUMN slug NVARCHAR(128)
        ");
    }
}