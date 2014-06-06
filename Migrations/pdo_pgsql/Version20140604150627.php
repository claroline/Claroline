<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/04 03:06:28
 */
class Version20140604150627 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_competence (
                id SERIAL NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                description TEXT DEFAULT NULL, 
                score INT NOT NULL, 
                isPlatform BOOLEAN DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F65DE32582D40A1F ON claro_competence (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_competence_users (
                id SERIAL NOT NULL, 
                competence_id INT DEFAULT NULL, 
                user_id INT NOT NULL, 
                score INT NOT NULL, 
                PRIMARY KEY(id)
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
        ");
        $this->addSql("
            CREATE TABLE claro_competence_hierarchy (
                id SERIAL NOT NULL, 
                competence_id INT NOT NULL, 
                parent_id INT DEFAULT NULL, 
                root_id INT DEFAULT NULL, 
                PRIMARY KEY(id)
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
        ");
        $this->addSql("
            ALTER TABLE claro_competence 
            ADD CONSTRAINT FK_F65DE32582D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_competence_users 
            ADD CONSTRAINT FK_2E80B8E215761DAB FOREIGN KEY (competence_id) 
            REFERENCES claro_competence (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_competence_users 
            ADD CONSTRAINT FK_2E80B8E2A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_competence_hierarchy 
            ADD CONSTRAINT FK_D4A415FD15761DAB FOREIGN KEY (competence_id) 
            REFERENCES claro_competence (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_competence_hierarchy 
            ADD CONSTRAINT FK_D4A415FD727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_competence (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_competence_hierarchy 
            ADD CONSTRAINT FK_D4A415FD79066886 FOREIGN KEY (root_id) 
            REFERENCES claro_competence (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace ALTER creation_date 
            DROP NOT NULL
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
            ALTER TABLE claro_workspace ALTER creation_date 
            SET 
                NOT NULL
        ");
    }
}