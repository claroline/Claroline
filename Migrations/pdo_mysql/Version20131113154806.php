<?php

namespace Icap\LessonBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2013/11/13 03:48:08
 */
class Version20131113154806 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            ADD slug VARCHAR(128) DEFAULT NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_3D7E3C8C989D9B62 ON icap__lesson_chapter (slug)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_3D7E3C8C989D9B62 ON icap__lesson_chapter
        ');
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            DROP slug
        ');
    }
}
