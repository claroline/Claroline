<?php

namespace Claroline\HomeBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/07/11 06:55:08
 */
final class Version20230426080000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro_home_tab (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                context VARCHAR(255) NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                class VARCHAR(255) DEFAULT NULL, 
                name VARCHAR(255) DEFAULT NULL, 
                longTitle LONGTEXT NOT NULL, 
                centerTitle TINYINT(1) NOT NULL, 
                showTitle TINYINT(1) DEFAULT 1 NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                entity_order INT NOT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                color VARCHAR(255) DEFAULT NULL, 
                icon VARCHAR(255) DEFAULT NULL, 
                hidden TINYINT(1) DEFAULT 0 NOT NULL, 
                accessible_from DATETIME DEFAULT NULL, 
                accessible_until DATETIME DEFAULT NULL, 
                access_code VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_A9744CCED17F50A6 (uuid), 
                INDEX IDX_A9744CCEA76ED395 (user_id), 
                INDEX IDX_A9744CCE82D40A1F (workspace_id), 
                INDEX IDX_A9744CCE727ACA70 (parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_home_tab_roles (
                hometab_id INT NOT NULL, 
                role_id INT NOT NULL, 
                INDEX IDX_B81359F3CCE862F (hometab_id), 
                INDEX IDX_B81359F3D60322AC (role_id), 
                PRIMARY KEY(hometab_id, role_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_home_tab_widgets (
                id INT AUTO_INCREMENT NOT NULL, 
                tab_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_E813FD848D0C9323 (tab_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE claro_home_tab_widgets_containers (
                tab_id INT NOT NULL, 
                container_id INT NOT NULL, 
                INDEX IDX_151555278D0C9323 (tab_id), 
                UNIQUE INDEX UNIQ_15155527BC21F742 (container_id), 
                PRIMARY KEY(tab_id, container_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            ADD CONSTRAINT FK_A9744CCEA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            ADD CONSTRAINT FK_A9744CCE82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            ADD CONSTRAINT FK_A9744CCE727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            ADD CONSTRAINT FK_B81359F3CCE862F FOREIGN KEY (hometab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            ADD CONSTRAINT FK_B81359F3D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_widgets 
            ADD CONSTRAINT FK_E813FD848D0C9323 FOREIGN KEY (tab_id) 
            REFERENCES claro_home_tab (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_widgets_containers 
            ADD CONSTRAINT FK_151555278D0C9323 FOREIGN KEY (tab_id) 
            REFERENCES claro_home_tab_widgets (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_widgets_containers 
            ADD CONSTRAINT FK_15155527BC21F742 FOREIGN KEY (container_id) 
            REFERENCES claro_widget_container (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_home_tab 
            DROP FOREIGN KEY FK_A9744CCEA76ED395
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            DROP FOREIGN KEY FK_A9744CCE82D40A1F
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab 
            DROP FOREIGN KEY FK_A9744CCE727ACA70
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            DROP FOREIGN KEY FK_B81359F3CCE862F
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_roles 
            DROP FOREIGN KEY FK_B81359F3D60322AC
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_widgets 
            DROP FOREIGN KEY FK_E813FD848D0C9323
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_widgets_containers 
            DROP FOREIGN KEY FK_151555278D0C9323
        ');
        $this->addSql('
            ALTER TABLE claro_home_tab_widgets_containers 
            DROP FOREIGN KEY FK_15155527BC21F742
        ');
        $this->addSql('
            DROP TABLE claro_home_tab
        ');
        $this->addSql('
            DROP TABLE claro_home_tab_roles
        ');
        $this->addSql('
            DROP TABLE claro_home_tab_widgets
        ');
        $this->addSql('
            DROP TABLE claro_home_tab_widgets_containers
        ');
    }
}
