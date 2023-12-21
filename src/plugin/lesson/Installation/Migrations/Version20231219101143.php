<?php

namespace Icap\LessonBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/12/19 10:11:43
 */
final class Version20231219101143 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            ADD customNumbering VARCHAR(255) NULL
        ');
        $this->addSql('
            ALTER TABLE icap__lesson
            ADD numbering VARCHAR(255) NOT NULL DEFAULT \'none\'
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            DROP customNumbering
        ');
        $this->addSql('
            ALTER TABLE icap__lesson
            DROP numbering
        ');
    }
}
