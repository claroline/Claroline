<?php

namespace Claroline\ProjectBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2026/04/06 04:08:28
 */
final class Version20260406160827 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE claro_project_criteria (
                label VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                score_max DOUBLE PRECISION NOT NULL, 
                position INT NOT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                project_id INT NOT NULL, 
                UNIQUE INDEX UNIQ_3B705CB7D17F50A6 (uuid), 
                INDEX IDX_3B705CB7166D1F9C (project_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_project_criteria_score (
                score DOUBLE PRECISION NOT NULL, 
                comment LONGTEXT DEFAULT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                correction_id INT NOT NULL, 
                criteria_id INT NOT NULL, 
                UNIQUE INDEX UNIQ_18C7027D17F50A6 (uuid), 
                INDEX IDX_18C702794AE086B (correction_id), 
                INDEX IDX_18C7027990BEA15 (criteria_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_project_milestone (
                title VARCHAR(255) NOT NULL, 
                instructions LONGTEXT DEFAULT NULL, 
                position INT NOT NULL, 
                deadline_type VARCHAR(255) NOT NULL, 
                deadline_date DATETIME DEFAULT NULL, 
                deadline_days INT DEFAULT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                project_id INT NOT NULL, 
                UNIQUE INDEX UNIQ_809B79DCD17F50A6 (uuid), 
                INDEX IDX_809B79DC166D1F9C (project_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_project_annotation (
                content LONGTEXT NOT NULL, 
                date DATETIME NOT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                submission_id INT NOT NULL, 
                milestone_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_A25875FBD17F50A6 (uuid), 
                INDEX IDX_A25875FBE1FD4933 (submission_id), 
                INDEX IDX_A25875FB4B3E2EDA (milestone_id), 
                INDEX IDX_A25875FBA76ED395 (user_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_project (
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                instruction LONGTEXT DEFAULT NULL, 
                submission_type VARCHAR(255) NOT NULL, 
                allowed_file_types JSON DEFAULT NULL, 
                template_file JSON DEFAULT NULL, 
                grading_mode VARCHAR(255) NOT NULL, 
                score_max DOUBLE PRECISION NOT NULL, 
                deadline_type VARCHAR(255) NOT NULL, 
                deadline_date DATETIME DEFAULT NULL, 
                deadline_days INT DEFAULT NULL, 
                estimated_duration INT DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_4FF06D49D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_4FF06D49B87FAB32 (resourceNode_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_project_submission (
                content_type VARCHAR(255) DEFAULT NULL, 
                text_content LONGTEXT DEFAULT NULL, 
                file_data JSON DEFAULT NULL, 
                url_content VARCHAR(255) DEFAULT NULL, 
                submitted_date DATETIME NOT NULL, 
                first_access_date DATETIME DEFAULT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                project_id INT NOT NULL, 
                milestone_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_571911FAD17F50A6 (uuid), 
                INDEX IDX_571911FA166D1F9C (project_id), 
                INDEX IDX_571911FA4B3E2EDA (milestone_id), 
                INDEX IDX_571911FAA76ED395 (user_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_project_correction (
                status VARCHAR(255) NOT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                comment LONGTEXT DEFAULT NULL, 
                start_date DATETIME NOT NULL, 
                last_edition_date DATETIME NOT NULL, 
                submission_date DATETIME DEFAULT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                project_id INT NOT NULL, 
                submission_id INT DEFAULT NULL, 
                milestone_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                corrector_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_2E81EAB1D17F50A6 (uuid), 
                INDEX IDX_2E81EAB1166D1F9C (project_id), 
                INDEX IDX_2E81EAB1E1FD4933 (submission_id), 
                INDEX IDX_2E81EAB14B3E2EDA (milestone_id), 
                INDEX IDX_2E81EAB1A76ED395 (user_id), 
                INDEX IDX_2E81EAB13A6E8746 (corrector_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_project_criteria 
            ADD CONSTRAINT FK_3B705CB7166D1F9C FOREIGN KEY (project_id) 
            REFERENCES claro_project (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_criteria_score 
            ADD CONSTRAINT FK_18C702794AE086B FOREIGN KEY (correction_id) 
            REFERENCES claro_project_correction (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_criteria_score 
            ADD CONSTRAINT FK_18C7027990BEA15 FOREIGN KEY (criteria_id) 
            REFERENCES claro_project_criteria (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_milestone 
            ADD CONSTRAINT FK_809B79DC166D1F9C FOREIGN KEY (project_id) 
            REFERENCES claro_project (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_annotation 
            ADD CONSTRAINT FK_A25875FBE1FD4933 FOREIGN KEY (submission_id) 
            REFERENCES claro_project_submission (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_annotation 
            ADD CONSTRAINT FK_A25875FB4B3E2EDA FOREIGN KEY (milestone_id) 
            REFERENCES claro_project_milestone (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_annotation 
            ADD CONSTRAINT FK_A25875FBA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_project 
            ADD CONSTRAINT FK_4FF06D49B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_submission 
            ADD CONSTRAINT FK_571911FA166D1F9C FOREIGN KEY (project_id) 
            REFERENCES claro_project (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_submission 
            ADD CONSTRAINT FK_571911FA4B3E2EDA FOREIGN KEY (milestone_id) 
            REFERENCES claro_project_milestone (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_submission 
            ADD CONSTRAINT FK_571911FAA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            ADD CONSTRAINT FK_2E81EAB1166D1F9C FOREIGN KEY (project_id) 
            REFERENCES claro_project (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            ADD CONSTRAINT FK_2E81EAB1E1FD4933 FOREIGN KEY (submission_id) 
            REFERENCES claro_project_submission (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            ADD CONSTRAINT FK_2E81EAB14B3E2EDA FOREIGN KEY (milestone_id) 
            REFERENCES claro_project_milestone (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            ADD CONSTRAINT FK_2E81EAB1A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            ADD CONSTRAINT FK_2E81EAB13A6E8746 FOREIGN KEY (corrector_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_project_criteria 
            DROP FOREIGN KEY FK_3B705CB7166D1F9C
        ");
        $this->addSql("
            ALTER TABLE claro_project_criteria_score 
            DROP FOREIGN KEY FK_18C702794AE086B
        ");
        $this->addSql("
            ALTER TABLE claro_project_criteria_score 
            DROP FOREIGN KEY FK_18C7027990BEA15
        ");
        $this->addSql("
            ALTER TABLE claro_project_milestone 
            DROP FOREIGN KEY FK_809B79DC166D1F9C
        ");
        $this->addSql("
            ALTER TABLE claro_project_annotation 
            DROP FOREIGN KEY FK_A25875FBE1FD4933
        ");
        $this->addSql("
            ALTER TABLE claro_project_annotation 
            DROP FOREIGN KEY FK_A25875FB4B3E2EDA
        ");
        $this->addSql("
            ALTER TABLE claro_project_annotation 
            DROP FOREIGN KEY FK_A25875FBA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_project 
            DROP FOREIGN KEY FK_4FF06D49B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_project_submission 
            DROP FOREIGN KEY FK_571911FA166D1F9C
        ");
        $this->addSql("
            ALTER TABLE claro_project_submission 
            DROP FOREIGN KEY FK_571911FA4B3E2EDA
        ");
        $this->addSql("
            ALTER TABLE claro_project_submission 
            DROP FOREIGN KEY FK_571911FAA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            DROP FOREIGN KEY FK_2E81EAB1166D1F9C
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            DROP FOREIGN KEY FK_2E81EAB1E1FD4933
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            DROP FOREIGN KEY FK_2E81EAB14B3E2EDA
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            DROP FOREIGN KEY FK_2E81EAB1A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            DROP FOREIGN KEY FK_2E81EAB13A6E8746
        ");
        $this->addSql("
            DROP TABLE claro_project_criteria
        ");
        $this->addSql("
            DROP TABLE claro_project_criteria_score
        ");
        $this->addSql("
            DROP TABLE claro_project_milestone
        ");
        $this->addSql("
            DROP TABLE claro_project_annotation
        ");
        $this->addSql("
            DROP TABLE claro_project
        ");
        $this->addSql("
            DROP TABLE claro_project_submission
        ");
        $this->addSql("
            DROP TABLE claro_project_correction
        ");
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
