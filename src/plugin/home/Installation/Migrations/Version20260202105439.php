<?php

namespace Claroline\HomeBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2026/02/02 10:54:40
 */
final class Version20260202105439 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_home_tab_view (
                seen_at DATETIME NOT NULL, 
                view_count INT NOT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                home_tab_id INT NOT NULL, 
                INDEX IDX_802AD86A76ED395 (user_id), 
                INDEX IDX_802AD867D08FA9E (home_tab_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_view 
            ADD CONSTRAINT FK_802AD86A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_view 
            ADD CONSTRAINT FK_802AD867D08FA9E FOREIGN KEY (home_tab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            ADD views INT DEFAULT 0 NOT NULL, 
            CHANGE class class VARCHAR(255) DEFAULT NULL, 
            CHANGE name name VARCHAR(255) DEFAULT NULL, 
            CHANGE poster poster VARCHAR(255) DEFAULT NULL, 
            CHANGE icon icon VARCHAR(255) DEFAULT NULL, 
            CHANGE accessible_from accessible_from DATETIME DEFAULT NULL, 
            CHANGE accessible_until accessible_until DATETIME DEFAULT NULL, 
            CHANGE access_code access_code VARCHAR(255) DEFAULT NULL, 
            CHANGE context_id context_id VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab_view 
            DROP FOREIGN KEY FK_802AD86A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_view 
            DROP FOREIGN KEY FK_802AD867D08FA9E
        ');
        $this->addSql('
            DROP TABLE claro_home_tab_view
        ');
        $this->addSql("
            ALTER TABLE claro_home_tab 
            DROP views, 
            CHANGE class class VARCHAR(255) DEFAULT 'NULL', 
            CHANGE name name VARCHAR(255) DEFAULT 'NULL', 
            CHANGE poster poster VARCHAR(255) DEFAULT 'NULL', 
            CHANGE icon icon VARCHAR(255) DEFAULT 'NULL', 
            CHANGE accessible_from accessible_from DATETIME DEFAULT 'NULL', 
            CHANGE accessible_until accessible_until DATETIME DEFAULT 'NULL', 
            CHANGE access_code access_code VARCHAR(255) DEFAULT 'NULL', 
            CHANGE context_id context_id VARCHAR(255) DEFAULT 'NULL'
        ");
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
