<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/25 10:49:32
 */
class Version20130925104931 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_widget_instance (
                id INT AUTO_INCREMENT NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                widget_id INT NOT NULL, 
                is_admin TINYINT(1) NOT NULL, 
                is_desktop TINYINT(1) NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                INDEX IDX_5F89A38582D40A1F (workspace_id), 
                INDEX IDX_5F89A385A76ED395 (user_id), 
                INDEX IDX_5F89A385FBE885E2 (widget_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_widget_instance 
            ADD CONSTRAINT FK_5F89A38582D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_instance 
            ADD CONSTRAINT FK_5F89A385A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_instance 
            ADD CONSTRAINT FK_5F89A385FBE885E2 FOREIGN KEY (widget_id) 
            REFERENCES claro_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            DROP FOREIGN KEY FK_11925ED382D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_11925ED382D40A1F ON simple_text_workspace_widget_config
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            DROP is_default, 
            CHANGE workspace_id displayConfig_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            ADD CONSTRAINT FK_11925ED3EF00646E FOREIGN KEY (displayConfig_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_11925ED3EF00646E ON simple_text_workspace_widget_config (displayConfig_id)
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP FOREIGN KEY FK_D48CC23EFBE885E2
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23EFBE885E2 ON claro_widget_home_tab_config
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config CHANGE widget_id widget_instance_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD CONSTRAINT FK_D48CC23E44BF891 FOREIGN KEY (widget_instance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23E44BF891 ON claro_widget_home_tab_config (widget_instance_id)
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD is_displayable_in_workspace TINYINT(1) NOT NULL, 
            ADD is_displayable_in_desktop TINYINT(1) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            DROP FOREIGN KEY FK_11925ED3EF00646E
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP FOREIGN KEY FK_D48CC23E44BF891
        ");
        $this->addSql("
            DROP TABLE claro_widget_instance
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP is_displayable_in_workspace, 
            DROP is_displayable_in_desktop
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23E44BF891 ON claro_widget_home_tab_config
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config CHANGE widget_instance_id widget_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD CONSTRAINT FK_D48CC23EFBE885E2 FOREIGN KEY (widget_id) 
            REFERENCES claro_widget (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23EFBE885E2 ON claro_widget_home_tab_config (widget_id)
        ");
        $this->addSql("
            DROP INDEX IDX_11925ED3EF00646E ON simple_text_workspace_widget_config
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            ADD is_default TINYINT(1) NOT NULL, 
            CHANGE displayconfig_id workspace_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            ADD CONSTRAINT FK_11925ED382D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_11925ED382D40A1F ON simple_text_workspace_widget_config (workspace_id)
        ");
    }
}