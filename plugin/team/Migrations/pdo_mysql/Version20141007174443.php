<?php

namespace Claroline\TeamBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/10/07 05:44:45
 */
class Version20141007174443 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_team (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT NOT NULL, 
                role_id INT DEFAULT NULL, 
                team_manager INT DEFAULT NULL, 
                team_manager_role INT DEFAULT NULL, 
                directory_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                max_users INT DEFAULT NULL, 
                self_registration TINYINT(1) NOT NULL, 
                self_unregistration TINYINT(1) NOT NULL, 
                is_public TINYINT(1) NOT NULL, 
                INDEX IDX_A2FE580482D40A1F (workspace_id), 
                UNIQUE INDEX UNIQ_A2FE5804D60322AC (role_id), 
                INDEX IDX_A2FE580455D548E (team_manager), 
                UNIQUE INDEX UNIQ_A2FE580459E625D1 (team_manager_role), 
                UNIQUE INDEX UNIQ_A2FE58042C94069F (directory_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_team_users (
                team_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_B10C67F3296CD8AE (team_id), 
                INDEX IDX_B10C67F3A76ED395 (user_id), 
                PRIMARY KEY(team_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_team_parameters (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT NOT NULL, 
                self_registration TINYINT(1) NOT NULL, 
                self_unregistration TINYINT(1) NOT NULL, 
                is_public TINYINT(1) NOT NULL, 
                max_teams INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_C99EF54182D40A1F (workspace_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE580482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE5804D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE580455D548E FOREIGN KEY (team_manager) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE580459E625D1 FOREIGN KEY (team_manager_role) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE58042C94069F FOREIGN KEY (directory_id) 
            REFERENCES claro_directory (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_team_users 
            ADD CONSTRAINT FK_B10C67F3296CD8AE FOREIGN KEY (team_id) 
            REFERENCES claro_team (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_team_users 
            ADD CONSTRAINT FK_B10C67F3A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_team_parameters 
            ADD CONSTRAINT FK_C99EF54182D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_team_users 
            DROP FOREIGN KEY FK_B10C67F3296CD8AE
        ');
        $this->addSql('
            DROP TABLE claro_team
        ');
        $this->addSql('
            DROP TABLE claro_team_users
        ');
        $this->addSql('
            DROP TABLE claro_team_parameters
        ');
    }
}
