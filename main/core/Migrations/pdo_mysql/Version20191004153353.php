<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/10/04 03:33:54
 */
class Version20191004153353 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_workspace_requirements (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT NOT NULL, 
                user_id INT DEFAULT NULL, 
                role_id INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_894BE409D17F50A6 (uuid), 
                INDEX IDX_894BE40982D40A1F (workspace_id), 
                INDEX IDX_894BE409A76ED395 (user_id), 
                INDEX IDX_894BE409D60322AC (role_id), 
                UNIQUE INDEX workspace_user_requirements (workspace_id, user_id), 
                UNIQUE INDEX workspace_role_requirements (workspace_id, role_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_required_resources (
                requirements_id INT NOT NULL, 
                resourcenode_id INT NOT NULL, 
                INDEX IDX_85A0B2D9296B0ED5 (requirements_id), 
                INDEX IDX_85A0B2D977C292AE (resourcenode_id), 
                PRIMARY KEY(
                    requirements_id, resourcenode_id
                )
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_workspace_evaluation (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                user_name VARCHAR(255) NOT NULL, 
                workspace_code VARCHAR(255) NOT NULL, 
                evaluation_date DATETIME DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INT DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                score_min DOUBLE PRECISION DEFAULT NULL, 
                score_max DOUBLE PRECISION DEFAULT NULL, 
                custom_score VARCHAR(255) DEFAULT NULL, 
                progression INT DEFAULT NULL, 
                progression_max INT DEFAULT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                UNIQUE INDEX UNIQ_E0FF6754D17F50A6 (uuid), 
                INDEX IDX_E0FF675482D40A1F (workspace_id), 
                INDEX IDX_E0FF6754A76ED395 (user_id), 
                UNIQUE INDEX workspace_user_evaluation (workspace_id, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_requirements 
            ADD CONSTRAINT FK_894BE40982D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_requirements 
            ADD CONSTRAINT FK_894BE409A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_requirements 
            ADD CONSTRAINT FK_894BE409D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_required_resources 
            ADD CONSTRAINT FK_85A0B2D9296B0ED5 FOREIGN KEY (requirements_id) 
            REFERENCES claro_workspace_requirements (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_required_resources 
            ADD CONSTRAINT FK_85A0B2D977C292AE FOREIGN KEY (resourcenode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            ADD CONSTRAINT FK_E0FF675482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_evaluation 
            ADD CONSTRAINT FK_E0FF6754A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_workspace_required_resources 
            DROP FOREIGN KEY FK_85A0B2D9296B0ED5
        ');
        $this->addSql('
            DROP TABLE claro_workspace_requirements
        ');
        $this->addSql('
            DROP TABLE claro_workspace_required_resources
        ');
        $this->addSql('
            DROP TABLE claro_workspace_evaluation
        ');
    }
}
