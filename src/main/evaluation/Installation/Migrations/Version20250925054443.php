<?php

namespace Claroline\EvaluationBundle\Installation\Migrations;

use Claroline\InstallationBundle\Migrations\Helper\ConditionalMigrationTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/09/25 05:44:45
 */
final class Version20250925054443 extends AbstractMigration
{
    use ConditionalMigrationTrait;

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_workspace_evaluation_sequence (
                workspace_evaluation_id INT NOT NULL, 
                sequence_evaluation_id INT NOT NULL, 
                INDEX IDX_DA47A21F17414BF (workspace_evaluation_id), 
                INDEX IDX_DA47A2113DBEF86 (sequence_evaluation_id), 
                PRIMARY KEY (
                    workspace_evaluation_id, sequence_evaluation_id
                )
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation_sequence 
            ADD CONSTRAINT FK_DA47A21F17414BF FOREIGN KEY (workspace_evaluation_id) 
            REFERENCES claro_workspace_evaluation (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation_sequence 
            ADD CONSTRAINT FK_DA47A2113DBEF86 FOREIGN KEY (sequence_evaluation_id) 
            REFERENCES claro_evaluation_sequence_evaluation (id) 
            ON DELETE CASCADE
        ');

        $this->addSql('
            INSERT INTO claro_workspace_evaluation_sequence (workspace_evaluation_id, sequence_evaluation_id)
            SELECT DISTINCT we.id AS workspace_evaluation_id, se.id AS sequence_evaluation_id
            FROM claro_evaluation_sequence_evaluation AS se 
            LEFT JOIN innova_path AS s ON (se.sequence_id = s.id)
            LEFT JOIN claro_workspace_evaluation AS we ON (we.workspace_id = s.workspace_id AND we.user_id = se.user_id)
            WHERE se.id IS NOT NULL
              AND we.id IS NOT NULL
        ');

        if (!$this->checkColumnExists('innova_path', 'archived', $this->connection)) {
            $this->addSql('
                ALTER TABLE innova_path 
                ADD archived TINYINT(1) NOT NULL
            ');

            $this->addSql('
                UPDATE innova_path SET archived = 0
            ');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation_sequence 
            DROP FOREIGN KEY FK_DA47A21F17414BF
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation_sequence 
            DROP FOREIGN KEY FK_DA47A2113DBEF86
        ');
        $this->addSql('
            DROP TABLE claro_workspace_evaluation_sequence
        ');
        $this->addSql('
            ALTER TABLE innova_path 
            DROP archived
        ');
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
