<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/03/13 11:43:08
 */
class Version20180313114306 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // updates widget configuration
        // FIXME : CHANGE plugin_id plugin_id INT NOT NULL breakx update (because of foreign key)
        // CHANGE plugin_id plugin_id INT NOT NULL,
        $this->addSql("
            ALTER TABLE claro_widget
            ADD class VARCHAR(255) DEFAULT NULL,
            ADD abstract TINYINT(1) NOT NULL,
            ADD context LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)',
            ADD uuid VARCHAR(36) NOT NULL,
            ADD tags LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)',
            DROP is_configurable,
            DROP is_displayable_in_workspace,
            DROP is_displayable_in_desktop,
            DROP default_width,
            DROP default_height
        ");
        $this->addSql('
            UPDATE claro_widget SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_76CA6C4FD17F50A6 ON claro_widget (uuid)
        ');

        // updates widget instances
        $this->addSql('
            ALTER TABLE claro_widget_instance
            DROP FOREIGN KEY FK_5F89A38582D40A1F
        ');
        $this->addSql('
            DROP INDEX IDX_5F89A38582D40A1F ON claro_widget_instance
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance
            DROP FOREIGN KEY FK_5F89A385A76ED395
        ');
        $this->addSql('
            DROP INDEX IDX_5F89A385A76ED395 ON claro_widget_instance
        ');
        $this->addSql("
            ALTER TABLE claro_widget_instance
            ADD color VARCHAR(255) DEFAULT NULL,
            ADD backgroundType VARCHAR(255) NOT NULL,
            ADD background VARCHAR(255) DEFAULT NULL,
            ADD uuid VARCHAR(36) NOT NULL,
            ADD display VARCHAR(255) NOT NULL,
            ADD availableDisplays LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)',
            DROP user_id,
            DROP is_admin,
            DROP is_desktop,
            DROP icon,
            DROP template,
            DROP workspace_id,
            CHANGE name widget_name VARCHAR(255) NOT NULL
        ");
        $this->addSql('
            UPDATE claro_widget_instance SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_5F89A385D17F50A6 ON claro_widget_instance (uuid)
        ');

        // list widget
        $this->addSql("
            CREATE TABLE claro_widget_list (
                id INT AUTO_INCREMENT NOT NULL,
                widgetInstance_id INT NOT NULL,
                filterable TINYINT(1) NOT NULL,
                sortable TINYINT(1) NOT NULL,
                paginated TINYINT(1) NOT NULL,
                pageSize INT NOT NULL,
                defaultFilters LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)',
                availableColumns LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)',
                displayedColumns LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)',
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            ALTER TABLE claro_widget_list
            ADD CONSTRAINT FK_57E3C2C6AB7B5A55 FOREIGN KEY (widgetInstance_id)
            REFERENCES claro_widget_instance (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_57E3C2C6AB7B5A55 ON claro_widget_list (widgetInstance_id)
        ');

        // profile widget
        $this->addSql('
            CREATE TABLE claro_widget_profile (
                id INT AUTO_INCREMENT NOT NULL,
                widgetInstance_id INT NOT NULL,
                user_id INT DEFAULT NULL,
                currentUser TINYINT(1) NOT NULL,
                INDEX IDX_8F55951FA76ED395 (user_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro_widget_profile
            ADD CONSTRAINT FK_8F55951FAB7B5A55 FOREIGN KEY (widgetInstance_id)
            REFERENCES claro_widget_instance (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_8F55951FAB7B5A55 ON claro_widget_profile (widgetInstance_id)
        ');
        $this->addSql('
            ALTER TABLE claro_widget_profile
            ADD CONSTRAINT FK_8F55951FA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE SET NULL
        ');

        // simple widget
        $this->addSql('
            CREATE TABLE claro_widget_simple (
                id INT AUTO_INCREMENT NOT NULL,
                widgetInstance_id INT NOT NULL,
                content LONGTEXT NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');

        $this->addSql('
            ALTER TABLE claro_widget_simple
            ADD CONSTRAINT FK_18CC1F0AAB7B5A55 FOREIGN KEY (widgetInstance_id)
            REFERENCES claro_widget_instance (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_18CC1F0AAB7B5A55 ON claro_widget_simple (widgetInstance_id)
        ');

        // drops old tables
        $this->addSql('
            DROP TABLE claro_widget_roles
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_76CA6C4FD17F50A6 ON claro_widget
        ');
        $this->addSql('
            ALTER TABLE claro_widget
            ADD is_displayable_in_workspace TINYINT(1) NOT NULL,
            ADD is_displayable_in_desktop TINYINT(1) NOT NULL,
            ADD default_width INT DEFAULT 4 NOT NULL,
            ADD default_height INT DEFAULT 3 NOT NULL,
            DROP class,
            DROP context,
            DROP uuid,
            CHANGE abstract is_configurable TINYINT(1) NOT NULL
        ');

        $this->addSql('
            DROP INDEX UNIQ_5F89A385D17F50A6 ON claro_widget_instance
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance
            ADD user_id INT DEFAULT NULL,
            ADD is_admin TINYINT(1) NOT NULL,
            ADD is_desktop TINYINT(1) NOT NULL,
            ADD name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci,
            ADD icon VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci,
            ADD template VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci,
            DROP widget_name,
            DROP color,
            DROP backgroundType,
            DROP background,
            DROP uuid
        ');
        $this->addSql('
            ALTER TABLE claro_widget_instance
            ADD CONSTRAINT FK_5F89A385A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_5F89A385A76ED395 ON claro_widget_instance (user_id)
        ');

        $this->addSql('
            DROP TABLE claro_widget_list
        ');
        $this->addSql('
            DROP TABLE claro_widget_profile
        ');
        $this->addSql('
            DROP TABLE claro_widget_simple
        ');
    }
}
