<?php

namespace Claroline\TeamBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/07 05:44:45
 */
class Version20141007174443 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_team (
                id SERIAL NOT NULL, 
                workspace_id INT NOT NULL, 
                role_id INT DEFAULT NULL, 
                team_manager INT DEFAULT NULL, 
                team_manager_role INT DEFAULT NULL, 
                directory_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                description TEXT DEFAULT NULL, 
                max_users INT DEFAULT NULL, 
                self_registration BOOLEAN NOT NULL, 
                self_unregistration BOOLEAN NOT NULL, 
                is_public BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A2FE580482D40A1F ON claro_team (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A2FE5804D60322AC ON claro_team (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A2FE580455D548E ON claro_team (team_manager)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A2FE580459E625D1 ON claro_team (team_manager_role)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A2FE58042C94069F ON claro_team (directory_id)
        ");
        $this->addSql("
            CREATE TABLE claro_team_users (
                team_id INT NOT NULL, 
                user_id INT NOT NULL, 
                PRIMARY KEY(team_id, user_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B10C67F3296CD8AE ON claro_team_users (team_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B10C67F3A76ED395 ON claro_team_users (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_team_parameters (
                id SERIAL NOT NULL, 
                workspace_id INT NOT NULL, 
                self_registration BOOLEAN NOT NULL, 
                self_unregistration BOOLEAN NOT NULL, 
                is_public BOOLEAN NOT NULL, 
                max_teams INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_C99EF54182D40A1F ON claro_team_parameters (workspace_id)
        ");
        $this->addSql("
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE580482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE5804D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE580455D548E FOREIGN KEY (team_manager) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE580459E625D1 FOREIGN KEY (team_manager_role) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE58042C94069F FOREIGN KEY (directory_id) 
            REFERENCES claro_directory (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_team_users 
            ADD CONSTRAINT FK_B10C67F3296CD8AE FOREIGN KEY (team_id) 
            REFERENCES claro_team (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_team_users 
            ADD CONSTRAINT FK_B10C67F3A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_team_parameters 
            ADD CONSTRAINT FK_C99EF54182D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_team_users 
            DROP CONSTRAINT FK_B10C67F3296CD8AE
        ");
        $this->addSql("
            DROP TABLE claro_team
        ");
        $this->addSql("
            DROP TABLE claro_team_users
        ");
        $this->addSql("
            DROP TABLE claro_team_parameters
        ");
    }
}