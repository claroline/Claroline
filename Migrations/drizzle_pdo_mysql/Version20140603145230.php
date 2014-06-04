<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/03 02:52:35
 */
class Version20140603145230 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_competence (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                description TEXT DEFAULT NULL, 
                score INT NOT NULL, 
                isPlatform BOOLEAN DEFAULT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_F65DE32582D40A1F (workspace_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_competence_users (
                id INT AUTO_INCREMENT NOT NULL, 
                competence_id INT DEFAULT NULL, 
                user_id INT NOT NULL, 
                score INT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_2E80B8E215761DAB (competence_id), 
                INDEX IDX_2E80B8E2A76ED395 (user_id), 
                UNIQUE INDEX competence_user_unique (competence_id, user_id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_competence_hierarchy (
                id INT AUTO_INCREMENT NOT NULL, 
                competence_id INT NOT NULL, 
                parent_id INT DEFAULT NULL, 
                root_id INT DEFAULT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_D4A415FD15761DAB (competence_id), 
                INDEX IDX_D4A415FD727ACA70 (parent_id), 
                INDEX IDX_D4A415FD79066886 (root_id), 
                UNIQUE INDEX competence_hrch_unique (
                    competence_id, parent_id, root_id
                )
            )
        ");
        $this->addSql("
            ALTER TABLE claro_competence 
            ADD CONSTRAINT FK_F65DE32582D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_competence_users 
            ADD CONSTRAINT FK_2E80B8E215761DAB FOREIGN KEY (competence_id) 
            REFERENCES claro_competence (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_competence_users 
            ADD CONSTRAINT FK_2E80B8E2A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_competence_hierarchy 
            ADD CONSTRAINT FK_D4A415FD15761DAB FOREIGN KEY (competence_id) 
            REFERENCES claro_competence (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_competence_hierarchy 
            ADD CONSTRAINT FK_D4A415FD727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_competence (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_competence_hierarchy 
            ADD CONSTRAINT FK_D4A415FD79066886 FOREIGN KEY (root_id) 
            REFERENCES claro_competence (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D2852181F3A64 ON claro_user
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP public_url, 
            DROP has_tuned_public_url
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF460F904B
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF54B9D732
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF460F904B FOREIGN KEY (license_id) 
            REFERENCES claro_license (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF54B9D732 FOREIGN KEY (icon_id) 
            REFERENCES claro_resource_icon (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP creation_date, 
            DROP description
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge 
            DROP comment
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_competence_users 
            DROP FOREIGN KEY FK_2E80B8E215761DAB
        ");
        $this->addSql("
            ALTER TABLE claro_competence_hierarchy 
            DROP FOREIGN KEY FK_D4A415FD15761DAB
        ");
        $this->addSql("
            ALTER TABLE claro_competence_hierarchy 
            DROP FOREIGN KEY FK_D4A415FD727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_competence_hierarchy 
            DROP FOREIGN KEY FK_D4A415FD79066886
        ");
        $this->addSql("
            DROP TABLE claro_competence
        ");
        $this->addSql("
            DROP TABLE claro_competence_users
        ");
        $this->addSql("
            DROP TABLE claro_competence_hierarchy
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF460F904B
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP FOREIGN KEY FK_A76799FF54B9D732
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF460F904B FOREIGN KEY (license_id) 
            REFERENCES claro_license (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD CONSTRAINT FK_A76799FF54B9D732 FOREIGN KEY (icon_id) 
            REFERENCES claro_resource_icon (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD public_url VARCHAR(255) DEFAULT NULL, 
            ADD has_tuned_public_url BOOLEAN NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852181F3A64 ON claro_user (public_url)
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge 
            ADD comment TEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD creation_date INT NOT NULL, 
            ADD description TEXT DEFAULT NULL
        ");
    }
}