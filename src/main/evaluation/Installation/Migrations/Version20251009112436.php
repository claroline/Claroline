<?php

namespace Claroline\EvaluationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/10/09 11:24:38
 */
final class Version20251009112436 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_evaluation_resource_parameters (
                id INT AUTO_INCREMENT NOT NULL, 
                scored TINYINT(1) NOT NULL, 
                score_total DOUBLE PRECISION DEFAULT NULL, 
                success_condition JSON DEFAULT NULL, 
                end_message LONGTEXT DEFAULT NULL, 
                success_message LONGTEXT DEFAULT NULL, 
                failure_message LONGTEXT DEFAULT NULL, 
                max_attempts INT DEFAULT NULL, 
                attempts_reached_message LONGTEXT DEFAULT NULL, 
                resource_id INT DEFAULT NULL, 
                INDEX IDX_CCD7F82E89329D25 (resource_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_evaluation_sequence_parameters (
                id INT AUTO_INCREMENT NOT NULL, 
                scored TINYINT(1) NOT NULL, 
                score_total DOUBLE PRECISION DEFAULT NULL, 
                success_condition JSON DEFAULT NULL, 
                end_message LONGTEXT DEFAULT NULL, 
                success_message LONGTEXT DEFAULT NULL, 
                failure_message LONGTEXT DEFAULT NULL, 
                certified TINYINT(1) DEFAULT 0 NOT NULL, 
                sequence_id INT DEFAULT NULL, 
                certificate_template_id INT DEFAULT NULL, 
                INDEX IDX_D5B85E0D98FB19AE (sequence_id), 
                INDEX IDX_D5B85E0D22DEAC6F (certificate_template_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_evaluation_workspace_parameters (
                id INT AUTO_INCREMENT NOT NULL, 
                scored TINYINT(1) NOT NULL, 
                score_total DOUBLE PRECISION DEFAULT NULL, 
                success_condition JSON DEFAULT NULL, 
                end_message LONGTEXT DEFAULT NULL, 
                success_message LONGTEXT DEFAULT NULL, 
                failure_message LONGTEXT DEFAULT NULL, 
                certified TINYINT(1) DEFAULT 0 NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                certificate_template_id INT DEFAULT NULL, 
                INDEX IDX_6D8669C782D40A1F (workspace_id), 
                INDEX IDX_6D8669C722DEAC6F (certificate_template_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_resource_parameters 
            ADD CONSTRAINT FK_CCD7F82E89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_sequence_parameters 
            ADD CONSTRAINT FK_D5B85E0D98FB19AE FOREIGN KEY (sequence_id) 
            REFERENCES innova_path (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_sequence_parameters 
            ADD CONSTRAINT FK_D5B85E0D22DEAC6F FOREIGN KEY (certificate_template_id) 
            REFERENCES claro_template (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_workspace_parameters 
            ADD CONSTRAINT FK_6D8669C782D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_workspace_parameters 
            ADD CONSTRAINT FK_6D8669C722DEAC6F FOREIGN KEY (certificate_template_id) 
            REFERENCES claro_template (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_evaluation_resource_parameters 
            DROP FOREIGN KEY FK_CCD7F82E89329D25
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_sequence_parameters 
            DROP FOREIGN KEY FK_D5B85E0D98FB19AE
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_sequence_parameters 
            DROP FOREIGN KEY FK_D5B85E0D22DEAC6F
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_workspace_parameters 
            DROP FOREIGN KEY FK_6D8669C782D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_workspace_parameters 
            DROP FOREIGN KEY FK_6D8669C722DEAC6F
        ');
        $this->addSql('
            DROP TABLE claro_evaluation_resource_parameters
        ');
        $this->addSql('
            DROP TABLE claro_evaluation_sequence_parameters
        ');
        $this->addSql('
            DROP TABLE claro_evaluation_workspace_parameters
        ');
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
