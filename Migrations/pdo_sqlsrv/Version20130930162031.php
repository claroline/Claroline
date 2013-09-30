<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/30 04:20:31
 */
class Version20130930162031 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_widget_instance (
                id INT IDENTITY NOT NULL, 
                workspace_id INT, 
                user_id INT, 
                widget_id INT NOT NULL, 
                is_admin BIT NOT NULL, 
                is_desktop BIT NOT NULL, 
                name NVARCHAR(255) NOT NULL, 
                PRIMARY KEY (id)
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
            ALTER TABLE claro_widget 
            ADD is_displayable_in_workspace BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD is_displayable_in_desktop BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD widget_instance_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP COLUMN widget_id
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP CONSTRAINT FK_D48CC23EFBE885E2
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_D48CC23EFBE885E2'
            ) 
            ALTER TABLE claro_widget_home_tab_config 
            DROP CONSTRAINT IDX_D48CC23EFBE885E2 ELSE 
            DROP INDEX IDX_D48CC23EFBE885E2 ON claro_widget_home_tab_config
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
            sp_RENAME 'simple_text_workspace_widget_config.workspace_id', 
            'displayConfig_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            DROP COLUMN is_default
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config ALTER COLUMN displayConfig_id INT
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            DROP CONSTRAINT FK_11925ED382D40A1F
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_11925ED382D40A1F'
            ) 
            ALTER TABLE simple_text_workspace_widget_config 
            DROP CONSTRAINT IDX_11925ED382D40A1F ELSE 
            DROP INDEX IDX_11925ED382D40A1F ON simple_text_workspace_widget_config
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
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP CONSTRAINT FK_D48CC23E44BF891
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            DROP CONSTRAINT FK_11925ED3EF00646E
        ");
        $this->addSql("
            DROP TABLE claro_widget_instance
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP COLUMN is_displayable_in_workspace
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP COLUMN is_displayable_in_desktop
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            ADD widget_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config 
            DROP COLUMN widget_instance_id
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_D48CC23E44BF891'
            ) 
            ALTER TABLE claro_widget_home_tab_config 
            DROP CONSTRAINT IDX_D48CC23E44BF891 ELSE 
            DROP INDEX IDX_D48CC23E44BF891 ON claro_widget_home_tab_config
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
            sp_RENAME 'simple_text_workspace_widget_config.displayconfig_id', 
            'workspace_id', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            ADD is_default BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config ALTER COLUMN workspace_id INT
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_11925ED3EF00646E'
            ) 
            ALTER TABLE simple_text_workspace_widget_config 
            DROP CONSTRAINT IDX_11925ED3EF00646E ELSE 
            DROP INDEX IDX_11925ED3EF00646E ON simple_text_workspace_widget_config
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