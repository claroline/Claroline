<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

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
                id SERIAL NOT NULL,
                amount INT NOT NULL,
                restrictions TEXT DEFAULT NULL,
                widgetInstance_id INT DEFAULT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C16334B2AB7B5A55 ON claro_log_widget_config (widgetInstance_id)
        ");
        $this->addSql("
            COMMENT ON COLUMN claro_log_widget_config.restrictions IS '(DC2Type:simple_array)'
        ");
        $this->addSql("
            CREATE TABLE claro_badge_rule (
                id SERIAL NOT NULL,
                badge_id INT NOT NULL,
                occurrence SMALLINT NOT NULL,
                action VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_805FCB8FF7A2C2FC ON claro_badge_rule (badge_id)
        ");
        $this->addSql("
            CREATE TABLE claro_widget_instance (
                id SERIAL NOT NULL,
                workspace_id INT DEFAULT NULL,
                user_id INT DEFAULT NULL,
                widget_id INT NOT NULL,
                is_admin BOOLEAN NOT NULL,
                is_desktop BOOLEAN NOT NULL,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_5F89A38582D40A1F ON claro_widget_instance (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5F89A385A76ED395 ON claro_widget_instance (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5F89A385FBE885E2 ON claro_widget_instance (widget_id)
        ");
        $this->addSql("
            CREATE TABLE claro_simple_text_widget_config (
                id SERIAL NOT NULL,
                content TEXT NOT NULL,
                widgetInstance_id INT DEFAULT NULL,
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C389EBCCAB7B5A55 ON claro_simple_text_widget_config (widgetInstance_id)
        ");
        $this->addSql("
            ALTER TABLE claro_log_widget_config
            ADD CONSTRAINT FK_C16334B2AB7B5A55 FOREIGN KEY (widgetInstance_id)
            REFERENCES claro_widget_instance (id)
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule
            ADD CONSTRAINT FK_805FCB8FF7A2C2FC FOREIGN KEY (badge_id)
            REFERENCES claro_badge (id)
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_instance
            ADD CONSTRAINT FK_5F89A38582D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_instance
            ADD CONSTRAINT FK_5F89A385A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_instance
            ADD CONSTRAINT FK_5F89A385FBE885E2 FOREIGN KEY (widget_id)
            REFERENCES claro_widget (id)
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_simple_text_widget_config
            ADD CONSTRAINT FK_C389EBCCAB7B5A55 FOREIGN KEY (widgetInstance_id)
            REFERENCES claro_widget_instance (id)
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_user
            ADD picture VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user
            ADD description TEXT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge
            ADD workspace_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge
            ADD automatic_award BOOLEAN DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge
            ADD CONSTRAINT FK_74F39F0F82D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE INDEX IDX_74F39F0F82D40A1F ON claro_badge (workspace_id)
        ");
        $this->addSql("
            ALTER TABLE claro_widget
            ADD is_displayable_in_workspace BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget
            ADD is_displayable_in_desktop BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            ADD widget_instance_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            DROP widget_id
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            DROP CONSTRAINT IF EXISTS FK_D48CC23EFBE885E2
        ");
        $this->addSql("
            DROP INDEX IF EXISTS IDX_D48CC23EFBE885E2
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            ADD CONSTRAINT FK_D48CC23E44BF891 FOREIGN KEY (widget_instance_id)
            REFERENCES claro_widget_instance (id)
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23E44BF891 ON claro_widget_home_tab_config (widget_instance_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log_widget_config
            DROP CONSTRAINT FK_C16334B2AB7B5A55
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            DROP CONSTRAINT FK_D48CC23E44BF891
        ");
        $this->addSql("
            ALTER TABLE claro_simple_text_widget_config
            DROP CONSTRAINT FK_C389EBCCAB7B5A55
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
            DROP workspace_id
        ");
        $this->addSql("
            ALTER TABLE claro_badge
            DROP automatic_award
        ");
        $this->addSql("
            ALTER TABLE claro_badge
            DROP CONSTRAINT FK_74F39F0F82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_74F39F0F82D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_user
            DROP picture
        ");
        $this->addSql("
            ALTER TABLE claro_user
            DROP description
        ");
        $this->addSql("
            ALTER TABLE claro_widget
            DROP is_displayable_in_workspace
        ");
        $this->addSql("
            ALTER TABLE claro_widget
            DROP is_displayable_in_desktop
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            ADD widget_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            DROP widget_instance_id
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23E44BF891
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            ADD CONSTRAINT FK_D48CC23EFBE885E2 FOREIGN KEY (widget_id)
            REFERENCES claro_widget (id)
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23EFBE885E2 ON claro_widget_home_tab_config (widget_id)
        ");
    }
}