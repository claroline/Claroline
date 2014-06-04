<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

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
                id INT IDENTITY NOT NULL, 
                workspace_id INT, 
                name NVARCHAR(255) NOT NULL, 
                description VARCHAR(MAX), 
                score INT NOT NULL, 
                isPlatform BIT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F65DE32582D40A1F ON claro_competence (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_competence_users (
                id INT IDENTITY NOT NULL, 
                competence_id INT, 
                user_id INT NOT NULL, 
                score INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_2E80B8E215761DAB ON claro_competence_users (competence_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2E80B8E2A76ED395 ON claro_competence_users (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX competence_user_unique ON claro_competence_users (competence_id, user_id) 
            WHERE competence_id IS NOT NULL 
            AND user_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_competence_hierarchy (
                id INT IDENTITY NOT NULL, 
                competence_id INT NOT NULL, 
                parent_id INT, 
                root_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D4A415FD15761DAB ON claro_competence_hierarchy (competence_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D4A415FD727ACA70 ON claro_competence_hierarchy (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D4A415FD79066886 ON claro_competence_hierarchy (root_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX competence_hrch_unique ON claro_competence_hierarchy (
                competence_id, parent_id, root_id
            ) 
            WHERE competence_id IS NOT NULL 
            AND parent_id IS NOT NULL 
            AND root_id IS NOT NULL
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
            ALTER TABLE claro_user 
            DROP COLUMN public_url
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN has_tuned_public_url
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_EB8D2852181F3A64'
            ) 
            ALTER TABLE claro_user 
            DROP CONSTRAINT UNIQ_EB8D2852181F3A64 ELSE 
            DROP INDEX UNIQ_EB8D2852181F3A64 ON claro_user
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT FK_A76799FF460F904B
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT FK_A76799FF54B9D732
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
            DROP COLUMN creation_date
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN description
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge 
            DROP COLUMN comment
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_competence_users 
            DROP CONSTRAINT FK_2E80B8E215761DAB
        ");
        $this->addSql("
            ALTER TABLE claro_competence_hierarchy 
            DROP CONSTRAINT FK_D4A415FD15761DAB
        ");
        $this->addSql("
            ALTER TABLE claro_competence_hierarchy 
            DROP CONSTRAINT FK_D4A415FD727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_competence_hierarchy 
            DROP CONSTRAINT FK_D4A415FD79066886
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
            DROP CONSTRAINT FK_A76799FF460F904B
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP CONSTRAINT FK_A76799FF54B9D732
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
            ADD public_url NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD has_tuned_public_url BIT NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852181F3A64 ON claro_user (public_url) 
            WHERE public_url IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge 
            ADD comment VARCHAR(MAX)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD creation_date INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD description VARCHAR(MAX)
        ");
    }
}