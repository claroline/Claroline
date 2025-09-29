<?php

namespace Claroline\EvaluationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/09/29 01:57:50
 */
final class Version20250929135748 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_evaluation_sequence_view (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                sequence_id INT NOT NULL, 
                view_count INT NOT NULL,
                seen_at DATETIME NOT NULL,
                INDEX IDX_132A064BA76ED395 (user_id), 
                INDEX IDX_132A064B98FB19AE (sequence_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_sequence_view 
            ADD CONSTRAINT FK_132A064BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_sequence_view 
            ADD CONSTRAINT FK_132A064B98FB19AE FOREIGN KEY (sequence_id) 
            REFERENCES innova_path (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_path 
            ADD views INT DEFAULT 0 NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_evaluation_sequence_view 
            DROP FOREIGN KEY FK_132A064BA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_sequence_view 
            DROP FOREIGN KEY FK_132A064B98FB19AE
        ');
        $this->addSql('
            DROP TABLE claro_evaluation_sequence_view
        ');
        $this->addSql('
            ALTER TABLE innova_path 
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
