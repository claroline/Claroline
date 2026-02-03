<?php

namespace Claroline\AnnouncementBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2026/01/26 10:24:36
 */
final class Version20260126102435 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_announcement_view (
                seen_at DATETIME NOT NULL, 
                view_count INT NOT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                announcement_id INT NOT NULL, 
                INDEX IDX_CC71C5BA76ED395 (user_id), 
                INDEX IDX_CC71C5B913AEA17 (announcement_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_announcement_view 
            ADD CONSTRAINT FK_CC71C5BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_announcement_view 
            ADD CONSTRAINT FK_CC71C5B913AEA17 FOREIGN KEY (announcement_id) 
            REFERENCES claro_announcement (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_announcements_send CHANGE data data JSON DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_announcement 
            ADD views INT DEFAULT 0 NOT NULL, 
            CHANGE title title VARCHAR(255) DEFAULT NULL, 
            CHANGE announcer announcer VARCHAR(255) DEFAULT NULL, 
            CHANGE createdAt createdAt DATETIME DEFAULT NULL, 
            CHANGE publication_date publication_date DATETIME DEFAULT NULL, 
            CHANGE visible_from visible_from DATETIME DEFAULT NULL, 
            CHANGE visible_until visible_until DATETIME DEFAULT NULL, 
            CHANGE poster poster VARCHAR(255) DEFAULT NULL, 
            CHANGE updatedAt updatedAt DATETIME DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_announcement_view 
            DROP FOREIGN KEY FK_CC71C5BA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_announcement_view 
            DROP FOREIGN KEY FK_CC71C5B913AEA17
        ');
        $this->addSql('
            DROP TABLE claro_announcement_view
        ');
        $this->addSql("
            ALTER TABLE claro_announcement 
            DROP views, 
            CHANGE title title VARCHAR(255) DEFAULT 'NULL', 
            CHANGE announcer announcer VARCHAR(255) DEFAULT 'NULL', 
            CHANGE publication_date publication_date DATETIME DEFAULT 'NULL', 
            CHANGE visible_from visible_from DATETIME DEFAULT 'NULL', 
            CHANGE visible_until visible_until DATETIME DEFAULT 'NULL', 
            CHANGE poster poster VARCHAR(255) DEFAULT 'NULL', 
            CHANGE createdAt createdAt DATETIME DEFAULT 'NULL', 
            CHANGE updatedAt updatedAt DATETIME DEFAULT 'NULL'
        ");
        $this->addSql("
            ALTER TABLE claro_announcements_send CHANGE data data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)'
        ");
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
