<?php

namespace Icap\LessonBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2026/02/26 09:22:16
 */
final class Version20260226092215 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__lesson_chapter CHANGE poster poster VARCHAR(255) DEFAULT NULL, 
            CHANGE customNumbering customNumbering VARCHAR(255) DEFAULT NULL, 
            CHANGE createdAt createdAt DATETIME DEFAULT NULL, 
            CHANGE updatedAt updatedAt DATETIME DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__lesson 
            ADD openSummary TINYINT NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__lesson 
            DROP openSummary
        ');
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
