<?php

namespace Claroline\EvaluationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/09/24 10:16:11
 */
final class Version20250924101608 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_path_progression 
            ADD sequence_evaluation_id INT DEFAULT NULL, 
            ADD resource_evaluation_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE innova_path_progression 
            ADD CONSTRAINT FK_960F966A13DBEF86 FOREIGN KEY (sequence_evaluation_id) 
            REFERENCES claro_evaluation_sequence_evaluation (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_path_progression 
            ADD CONSTRAINT FK_960F966AC807080 FOREIGN KEY (resource_evaluation_id) 
            REFERENCES claro_resource_user_evaluation (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            CREATE INDEX IDX_960F966A13DBEF86 ON innova_path_progression (sequence_evaluation_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_960F966AC807080 ON innova_path_progression (resource_evaluation_id)
        ');

        // update step progression with resource evaluation id
        $this->addSql('
            UPDATE innova_path_progression AS p
            LEFT JOIN innova_step AS ss ON (p.step_id = ss.id)
            LEFT JOIN claro_resource_user_evaluation AS e ON (e.resource_node = ss.resource_id AND e.user_id = p.user_id)
            SET p.resource_evaluation_id = e.id
            WHERE ss.resource_id IS NOT NULL
              AND e.id IS NOT NULL
        ');

        // insert missing step progression
        $this->addSql('
            INSERT INTO innova_path_progression (step_id, user_id, resource_evaluation_id, progression_status)
            SELECT s.id AS step_id, e.user_id, e.id AS resource_evaluation_id, "not_attempted" AS progression_status
            FROM claro_resource_user_evaluation AS e
            LEFT JOIN innova_step AS s ON s.resource_id = e.resource_node
            WHERE s.id IS NOT NULL
              AND e.resource_node IS NOT NULL
              AND e.user_id IS NOT NULL
              AND NOT EXISTS (SELECT p.id FROM innova_path_progression AS p WHERE p.step_id = s.id AND p.user_id = e.user_id)
        ');

        // link step progression to the parent sequence evaluation
        $this->addSql('
            UPDATE innova_path_progression AS p
            LEFT JOIN innova_step AS ss ON (p.step_id = ss.id)
            LEFT JOIN innova_path AS s ON (ss.path_id = s.id)
            LEFT JOIN claro_evaluation_sequence_evaluation AS e ON (e.sequence_id = s.id AND e.user_id = p.user_id)
            SET p.sequence_evaluation_id = e.id
            WHERE e.id IS NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_path_progression 
            DROP FOREIGN KEY FK_960F966A13DBEF86
        ');
        $this->addSql('
            ALTER TABLE innova_path_progression 
            DROP FOREIGN KEY FK_960F966AC807080
        ');
        $this->addSql('
            DROP INDEX IDX_960F966A13DBEF86 ON innova_path_progression
        ');
        $this->addSql('
            DROP INDEX IDX_960F966AC807080 ON innova_path_progression
        ');
        $this->addSql('
            ALTER TABLE innova_path_progression 
            DROP sequence_evaluation_id, 
            DROP resource_evaluation_id
        ');
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
