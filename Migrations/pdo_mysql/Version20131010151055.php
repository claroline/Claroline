<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/10 03:10:55
 */
class Version20131010151055 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_log_widget_config (
                id INT AUTO_INCREMENT NOT NULL, 
                amount INT NOT NULL, 
                restrictions LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', 
                widgetInstance_id INT DEFAULT NULL, 
                INDEX IDX_C16334B2AB7B5A55 (widgetInstance_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_badge_rule (
                id INT AUTO_INCREMENT NOT NULL, 
                badge_id INT NOT NULL, 
                occurrence SMALLINT NOT NULL, 
                action VARCHAR(255) NOT NULL, 
                INDEX IDX_805FCB8FF7A2C2FC (badge_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
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
            CREATE TABLE claro_simple_text_widget_config (
                id INT AUTO_INCREMENT NOT NULL, 
                content LONGTEXT NOT NULL, 
                widgetInstance_id INT DEFAULT NULL, 
                INDEX IDX_C389EBCCAB7B5A55 (widgetInstance_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_log_widget_config 
            ADD CONSTRAINT FK_C16334B2AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id) 
            ON DELETE CASCADE
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
            ALTER TABLE claro_simple_text_widget_config 
            ADD CONSTRAINT FK_C389EBCCAB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD picture VARCHAR(255) DEFAULT NULL, 
            ADD description LONGTEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD workspace_id INT DEFAULT NULL, 
            ADD automatic_award TINYINT(1) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            ADD CONSTRAINT FK_74F39F0F82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_74F39F0F82D40A1F ON claro_badge (workspace_id)
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD is_displayable_in_workspace TINYINT(1) NOT NULL, 
            ADD is_displayable_in_desktop TINYINT(1) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP FOREIGN KEY FK_D48CC23EFBE885E2
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23EFBE885E2 ON claro_widget_home_tab_config
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD widget_instance_id INT DEFAULT NULL, 
            DROP widget_id
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
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log_widget_config 
            DROP FOREIGN KEY FK_C16334B2AB7B5A55
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP FOREIGN KEY FK_D48CC23E44BF891
        ");
        $this->addSql("
            ALTER TABLE claro_simple_text_widget_config 
            DROP FOREIGN KEY FK_C389EBCCAB7B5A55
        ");
        $this->addSql("
            DROP TABLE claro_log_widget_config
        ");
        $this->addSql("
            DROP TABLE claro_badge_rule
        ");
        $this->addSql("
            DROP TABLE claro_widget_instance
        ");
        $this->addSql("
            DROP TABLE claro_simple_text_widget_config
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP FOREIGN KEY FK_74F39F0F82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_74F39F0F82D40A1F ON claro_badge
        ");
        $this->addSql("
            ALTER TABLE claro_badge 
            DROP workspace_id, 
            DROP automatic_award
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP picture, 
            DROP description
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
            ALTER TABLE claro_widget_home_tab_config 
            ADD widget_id INT NOT NULL, 
            DROP widget_instance_id
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
    }
}