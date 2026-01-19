<?php

namespace Icap\LessonBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/02/03 10:17:07
 */
final class Version20250203101702 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            ADD createdAt DATETIME DEFAULT NULL, 
            ADD updatedAt DATETIME DEFAULT NULL, 
            ADD creator_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            ADD CONSTRAINT FK_3D7E3C8C61220EA6 FOREIGN KEY (creator_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_3D7E3C8C61220EA6 ON icap__lesson_chapter (creator_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            DROP FOREIGN KEY FK_3D7E3C8C61220EA6
        ');
        $this->addSql('
            DROP INDEX IDX_3D7E3C8C61220EA6 ON icap__lesson_chapter
        ');
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            DROP createdAt, 
            DROP updatedAt, 
            DROP creator_id
        ');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
