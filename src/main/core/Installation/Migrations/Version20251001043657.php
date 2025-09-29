<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/10/01 04:36:59
 */
final class Version20251001043657 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_resource_view (
                seen_at DATETIME NOT NULL, 
                view_count INT NOT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                resource_id INT NOT NULL, 
                INDEX IDX_DCE5DA34A76ED395 (user_id), 
                INDEX IDX_DCE5DA3489329D25 (resource_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_view (
                seen_at DATETIME NOT NULL, 
                view_count INT NOT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                workspace_id INT NOT NULL, 
                INDEX IDX_BA32E09DA76ED395 (user_id), 
                INDEX IDX_BA32E09D82D40A1F (workspace_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_resource_view 
            ADD CONSTRAINT FK_DCE5DA34A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_view 
            ADD CONSTRAINT FK_DCE5DA3489329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_view 
            ADD CONSTRAINT FK_BA32E09DA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_view 
            ADD CONSTRAINT FK_BA32E09D82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            ADD views INT DEFAULT 0 NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node CHANGE views_count views INT DEFAULT 0 NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_view 
            DROP FOREIGN KEY FK_DCE5DA34A76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_resource_view 
            DROP FOREIGN KEY FK_DCE5DA3489329D25
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_view 
            DROP FOREIGN KEY FK_BA32E09DA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_view 
            DROP FOREIGN KEY FK_BA32E09D82D40A1F
        ');
        $this->addSql('
            DROP TABLE claro_resource_view
        ');
        $this->addSql('
            DROP TABLE claro_workspace_view
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node CHANGE views views_count INT DEFAULT 0 NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace 
            DROP views
        ');
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
