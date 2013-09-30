<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

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
            ALTER TABLE simple_text_workspace_widget_config 
            DROP is_default
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config RENAME COLUMN workspace_id TO displayConfig_id
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            DROP CONSTRAINT FK_11925ED382D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_11925ED382D40A1F
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            ADD CONSTRAINT FK_11925ED3EF00646E FOREIGN KEY (displayConfig_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE INDEX IDX_11925ED3EF00646E ON simple_text_workspace_widget_config (displayConfig_id)
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config RENAME COLUMN widget_id TO widget_instance_id
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP CONSTRAINT FK_D48CC23EFBE885E2
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23EFBE885E2
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
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD is_displayable_in_workspace BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD is_displayable_in_desktop BOOLEAN NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            DROP CONSTRAINT FK_11925ED3EF00646E
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP CONSTRAINT FK_D48CC23E44BF891
        ");
        $this->addSql("
            DROP TABLE claro_widget_instance
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
            ALTER TABLE claro_widget_home_tab_config RENAME COLUMN widget_instance_id TO widget_id
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
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            ADD is_default BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config RENAME COLUMN displayconfig_id TO workspace_id
        ");
        $this->addSql("
            DROP INDEX IDX_11925ED3EF00646E
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            ADD CONSTRAINT FK_11925ED382D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE INDEX IDX_11925ED382D40A1F ON simple_text_workspace_widget_config (workspace_id)
        ");
    }
}