<?php

namespace Icap\LessonBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2026/01/19 06:01:36
 */
final class Version20260119180135 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_chapter_view (
                seen_at DATETIME NOT NULL, 
                view_count INT NOT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                chapter_id INT NOT NULL, 
                INDEX IDX_E83252E4A76ED395 (user_id), 
                INDEX IDX_E83252E4579F4768 (chapter_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_chapter_view 
            ADD CONSTRAINT FK_E83252E4A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_chapter_view 
            ADD CONSTRAINT FK_E83252E4579F4768 FOREIGN KEY (chapter_id) 
            REFERENCES icap__lesson_chapter (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE icap__lesson_chapter CHANGE poster poster VARCHAR(255) DEFAULT NULL, 
            CHANGE customNumbering customNumbering VARCHAR(255) DEFAULT NULL, 
            CHANGE createdAt createdAt DATETIME DEFAULT NULL, 
            CHANGE updatedAt updatedAt DATETIME DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__lesson CHANGE numbering numbering VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__lesson_chapter 
            ADD views INT DEFAULT 0 NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_chapter_view 
            DROP FOREIGN KEY FK_E83252E4A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_chapter_view 
            DROP FOREIGN KEY FK_E83252E4579F4768
        ');
        $this->addSql('
            DROP TABLE claro_chapter_view
        ');
        $this->addSql("
            ALTER TABLE icap__lesson CHANGE numbering numbering VARCHAR(255) DEFAULT '''none''' NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__lesson_chapter CHANGE customNumbering customNumbering VARCHAR(255) DEFAULT 'NULL', 
            CHANGE poster poster VARCHAR(255) DEFAULT 'NULL', 
            CHANGE createdAt createdAt DATETIME DEFAULT 'NULL', 
            CHANGE updatedAt updatedAt DATETIME DEFAULT 'NULL'
        ");
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
