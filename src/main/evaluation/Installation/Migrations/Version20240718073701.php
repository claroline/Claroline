<?php

namespace Claroline\EvaluationBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/07/18 07:38:10
 */
final class Version20240718073701 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_evaluation_skills_frameworks_workspaces (
                skills_framework_id INT NOT NULL, 
                workspace_id INT NOT NULL, 
                INDEX IDX_C1D2225764F84992 (skills_framework_id), 
                INDEX IDX_C1D2225782D40A1F (workspace_id), 
                PRIMARY KEY(
                    skills_framework_id, workspace_id
                )
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_skills_frameworks_workspaces 
            ADD CONSTRAINT FK_C1D2225764F84992 FOREIGN KEY (skills_framework_id) 
            REFERENCES claro_evaluation_skills_framework (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_skills_frameworks_workspaces 
            ADD CONSTRAINT FK_C1D2225782D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_evaluation_skills_frameworks_workspaces 
            DROP FOREIGN KEY FK_C1D2225764F84992
        ');
        $this->addSql('
            ALTER TABLE claro_evaluation_skills_frameworks_workspaces 
            DROP FOREIGN KEY FK_C1D2225782D40A1F
        ');
        $this->addSql('
            DROP TABLE claro_evaluation_skills_frameworks_workspaces
        ');
    }
}
