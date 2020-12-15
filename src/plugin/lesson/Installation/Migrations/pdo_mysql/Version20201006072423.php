<?php

namespace Icap\LessonBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/10/06 07:24:42
 */
class Version20201006072423 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            ADD internalNote LONGTEXT DEFAULT NULL,
            ADD poster VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__lesson 
            ADD description LONGTEXT DEFAULT NULL, 
            ADD show_overview TINYINT(1) DEFAULT "1" NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__lesson 
            DROP description, 
            DROP show_overview
        ');
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            DROP internalNote,
            DROP poster
        ');
    }
}
