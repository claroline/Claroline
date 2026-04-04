<?php

namespace Claroline\ProjectBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2026/04/04 01:39:24
 */
final class Version20260404133924 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE claro_project_milestone (
                position INT NOT NULL, 
                instruction LONGTEXT DEFAULT NULL, 
                deadline DATETIME DEFAULT NULL, 
                annotation_categories JSON NOT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                project_id INT NOT NULL, 
                UNIQUE INDEX UNIQ_809B79DCD17F50A6 (uuid), 
                INDEX IDX_809B79DC166D1F9C (project_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_project_grade (
                value DOUBLE PRECISION NOT NULL, 
                comment LONGTEXT DEFAULT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                correction_id INT NOT NULL, 
                criterion_id INT NOT NULL, 
                UNIQUE INDEX UNIQ_673776AAD17F50A6 (uuid), 
                INDEX IDX_673776AA94AE086B (correction_id), 
                INDEX IDX_673776AA97766307 (criterion_id), 
                UNIQUE INDEX UNIQ_673776AA9776630794AE086B (criterion_id, correction_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_project_annotation (
                category VARCHAR(255) DEFAULT NULL, 
                content LONGTEXT NOT NULL, 
                creation_date DATETIME NOT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                drop_id INT NOT NULL, 
                corrector_id INT NOT NULL, 
                UNIQUE INDEX UNIQ_A25875FBD17F50A6 (uuid), 
                INDEX IDX_A25875FB4D224760 (drop_id), 
                INDEX IDX_A25875FB3A6E8746 (corrector_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_project (
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                instruction LONGTEXT DEFAULT NULL, 
                expected_format VARCHAR(255) NOT NULL, 
                evaluation_type VARCHAR(255) NOT NULL, 
                drop_start_date DATETIME DEFAULT NULL, 
                drop_end_date DATETIME DEFAULT NULL, 
                estimated_duration INT DEFAULT NULL, 
                allow_file_upload TINYINT NOT NULL, 
                allow_rich_text TINYINT NOT NULL, 
                allow_url TINYINT NOT NULL, 
                allow_media TINYINT NOT NULL, 
                group_mode TINYINT NOT NULL, 
                iterative_mode TINYINT NOT NULL, 
                template_file JSON DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_4FF06D49D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_4FF06D49B87FAB32 (resourceNode_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_project_evaluators (
                project_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_7D8920B166D1F9C (project_id), 
                INDEX IDX_7D8920BA76ED395 (user_id), 
                PRIMARY KEY (project_id, user_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_project_document (
                type VARCHAR(255) NOT NULL, 
                file JSON DEFAULT NULL, 
                url VARCHAR(2048) DEFAULT NULL, 
                content LONGTEXT DEFAULT NULL, 
                drop_date DATETIME NOT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                drop_id INT NOT NULL, 
                UNIQUE INDEX UNIQ_55064D40D17F50A6 (uuid), 
                INDEX IDX_55064D404D224760 (drop_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_project_criterion (
                label VARCHAR(255) NOT NULL, 
                score_max DOUBLE PRECISION NOT NULL, 
                position INT NOT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                project_id INT NOT NULL, 
                UNIQUE INDEX UNIQ_B3B5D82FD17F50A6 (uuid), 
                INDEX IDX_B3B5D82F166D1F9C (project_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_project_drop (
                drop_date DATETIME NOT NULL, 
                finished TINYINT NOT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                project_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                team_id INT DEFAULT NULL, 
                milestone_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_73BA40C5D17F50A6 (uuid), 
                INDEX IDX_73BA40C5166D1F9C (project_id), 
                INDEX IDX_73BA40C5A76ED395 (user_id), 
                INDEX IDX_73BA40C5296CD8AE (team_id), 
                INDEX IDX_73BA40C54B3E2EDA (milestone_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_project_correction (
                score DOUBLE PRECISION DEFAULT NULL, 
                comment LONGTEXT DEFAULT NULL, 
                status VARCHAR(255) NOT NULL, 
                start_date DATETIME NOT NULL, 
                last_edition_date DATETIME NOT NULL, 
                submission_date DATETIME DEFAULT NULL, 
                id INT AUTO_INCREMENT NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                project_id INT NOT NULL, 
                drop_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                team_id INT DEFAULT NULL, 
                corrector_id INT NOT NULL, 
                UNIQUE INDEX UNIQ_2E81EAB1D17F50A6 (uuid), 
                INDEX IDX_2E81EAB1166D1F9C (project_id), 
                INDEX IDX_2E81EAB14D224760 (drop_id), 
                INDEX IDX_2E81EAB1A76ED395 (user_id), 
                INDEX IDX_2E81EAB1296CD8AE (team_id), 
                INDEX IDX_2E81EAB13A6E8746 (corrector_id), 
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_project_milestone 
            ADD CONSTRAINT FK_809B79DC166D1F9C FOREIGN KEY (project_id) 
            REFERENCES claro_project (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_grade 
            ADD CONSTRAINT FK_673776AA94AE086B FOREIGN KEY (correction_id) 
            REFERENCES claro_project_correction (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_grade 
            ADD CONSTRAINT FK_673776AA97766307 FOREIGN KEY (criterion_id) 
            REFERENCES claro_project_criterion (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_annotation 
            ADD CONSTRAINT FK_A25875FB4D224760 FOREIGN KEY (drop_id) 
            REFERENCES claro_project_drop (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_annotation 
            ADD CONSTRAINT FK_A25875FB3A6E8746 FOREIGN KEY (corrector_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project 
            ADD CONSTRAINT FK_4FF06D49B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_evaluators 
            ADD CONSTRAINT FK_7D8920B166D1F9C FOREIGN KEY (project_id) 
            REFERENCES claro_project (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_evaluators 
            ADD CONSTRAINT FK_7D8920BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_document 
            ADD CONSTRAINT FK_55064D404D224760 FOREIGN KEY (drop_id) 
            REFERENCES claro_project_drop (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_criterion 
            ADD CONSTRAINT FK_B3B5D82F166D1F9C FOREIGN KEY (project_id) 
            REFERENCES claro_project (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_drop 
            ADD CONSTRAINT FK_73BA40C5166D1F9C FOREIGN KEY (project_id) 
            REFERENCES claro_project (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_project_drop 
            ADD CONSTRAINT FK_73BA40C5A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_project_drop 
            ADD CONSTRAINT FK_73BA40C5296CD8AE FOREIGN KEY (team_id) 
            REFERENCES claro_team (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_project_drop 
            ADD CONSTRAINT FK_73BA40C54B3E2EDA FOREIGN KEY (milestone_id) 
            REFERENCES claro_project_milestone (id) 
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
            ADD CONSTRAINT FK_2E81EAB14D224760 FOREIGN KEY (drop_id) 
            REFERENCES claro_project_drop (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            ADD CONSTRAINT FK_2E81EAB1A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            ADD CONSTRAINT FK_2E81EAB1296CD8AE FOREIGN KEY (team_id) 
            REFERENCES claro_team (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            ADD CONSTRAINT FK_2E81EAB13A6E8746 FOREIGN KEY (corrector_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_project_milestone 
            DROP FOREIGN KEY FK_809B79DC166D1F9C
        ");
        $this->addSql("
            ALTER TABLE claro_project_grade 
            DROP FOREIGN KEY FK_673776AA94AE086B
        ");
        $this->addSql("
            ALTER TABLE claro_project_grade 
            DROP FOREIGN KEY FK_673776AA97766307
        ");
        $this->addSql("
            ALTER TABLE claro_project_annotation 
            DROP FOREIGN KEY FK_A25875FB4D224760
        ");
        $this->addSql("
            ALTER TABLE claro_project_annotation 
            DROP FOREIGN KEY FK_A25875FB3A6E8746
        ");
        $this->addSql("
            ALTER TABLE claro_project 
            DROP FOREIGN KEY FK_4FF06D49B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_project_evaluators 
            DROP FOREIGN KEY FK_7D8920B166D1F9C
        ");
        $this->addSql("
            ALTER TABLE claro_project_evaluators 
            DROP FOREIGN KEY FK_7D8920BA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_project_document 
            DROP FOREIGN KEY FK_55064D404D224760
        ");
        $this->addSql("
            ALTER TABLE claro_project_criterion 
            DROP FOREIGN KEY FK_B3B5D82F166D1F9C
        ");
        $this->addSql("
            ALTER TABLE claro_project_drop 
            DROP FOREIGN KEY FK_73BA40C5166D1F9C
        ");
        $this->addSql("
            ALTER TABLE claro_project_drop 
            DROP FOREIGN KEY FK_73BA40C5A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_project_drop 
            DROP FOREIGN KEY FK_73BA40C5296CD8AE
        ");
        $this->addSql("
            ALTER TABLE claro_project_drop 
            DROP FOREIGN KEY FK_73BA40C54B3E2EDA
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            DROP FOREIGN KEY FK_2E81EAB1166D1F9C
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            DROP FOREIGN KEY FK_2E81EAB14D224760
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            DROP FOREIGN KEY FK_2E81EAB1A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            DROP FOREIGN KEY FK_2E81EAB1296CD8AE
        ");
        $this->addSql("
            ALTER TABLE claro_project_correction 
            DROP FOREIGN KEY FK_2E81EAB13A6E8746
        ");
        $this->addSql("
            DROP TABLE claro_project_milestone
        ");
        $this->addSql("
            DROP TABLE claro_project_grade
        ");
        $this->addSql("
            DROP TABLE claro_project_annotation
        ");
        $this->addSql("
            DROP TABLE claro_project
        ");
        $this->addSql("
            DROP TABLE claro_project_evaluators
        ");
        $this->addSql("
            DROP TABLE claro_project_document
        ");
        $this->addSql("
            DROP TABLE claro_project_criterion
        ");
        $this->addSql("
            DROP TABLE claro_project_drop
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
