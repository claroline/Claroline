<?php

namespace Claroline\CommunityBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/10 09:18:11
 */
final class Version20221209094218 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_team (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT NOT NULL, 
                role_id INT DEFAULT NULL, 
                manager_role_id INT DEFAULT NULL, 
                directory_id INT DEFAULT NULL, 
                max_users INT DEFAULT NULL, 
                self_registration TINYINT(1) NOT NULL, 
                self_unregistration TINYINT(1) NOT NULL, 
                is_public TINYINT(1) NOT NULL, 
                dir_deletable TINYINT(1) DEFAULT 0 NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_name VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                thumbnail VARCHAR(255) DEFAULT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_A2FE5804D17F50A6 (uuid), 
                INDEX IDX_A2FE580482D40A1F (workspace_id), 
                UNIQUE INDEX UNIQ_A2FE5804D60322AC (role_id), 
                UNIQUE INDEX UNIQ_A2FE580468CE17BA (manager_role_id), 
                UNIQUE INDEX UNIQ_A2FE58042C94069F (directory_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_team_users (
                team_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_B10C67F3296CD8AE (team_id), 
                INDEX IDX_B10C67F3A76ED395 (user_id), 
                PRIMARY KEY(team_id, user_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
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
            ADD CONSTRAINT FK_A2FE580468CE17BA FOREIGN KEY (manager_role_id) 
            REFERENCES claro_role (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            ADD CONSTRAINT FK_A2FE58042C94069F FOREIGN KEY (directory_id) 
            REFERENCES claro_resource_node (id) 
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
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_team 
            DROP FOREIGN KEY FK_A2FE580482D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            DROP FOREIGN KEY FK_A2FE5804D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            DROP FOREIGN KEY FK_A2FE580468CE17BA
        ');
        $this->addSql('
            ALTER TABLE claro_team 
            DROP FOREIGN KEY FK_A2FE58042C94069F
        ');
        $this->addSql('
            ALTER TABLE claro_team_users 
            DROP FOREIGN KEY FK_B10C67F3296CD8AE
        ');
        $this->addSql('
            ALTER TABLE claro_team_users 
            DROP FOREIGN KEY FK_B10C67F3A76ED395
        ');
        $this->addSql('
            DROP TABLE claro_team
        ');
        $this->addSql('
            DROP TABLE claro_team_users
        ');
    }
}
