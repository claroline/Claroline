<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/04/21 08:37:49
 */
class Version20230421083732 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_workspace_shortcuts CHANGE shortcuts_data shortcuts_data LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            ALTER TABLE claro_connection_message_slide CHANGE shortcuts shortcuts LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_options CHANGE details details LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)", 
            CHANGE breadcrumbItems breadcrumbItems LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            ALTER TABLE claro_data_source CHANGE context context LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
            CHANGE tags tags LONGTEXT NOT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            ALTER TABLE claro_log CHANGE details details LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            ALTER TABLE claro_menu_action CHANGE scope scope LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
            CHANGE api api LONGTEXT NOT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            ALTER TABLE claro_resource_evaluation CHANGE more_data more_data LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node CHANGE accesses accesses LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            ALTER TABLE claro_resource_type CHANGE tags tags LONGTEXT NOT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            ALTER TABLE claro_widget CHANGE sources sources LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
            CHANGE context context LONGTEXT NOT NULL COMMENT "(DC2Type:json)", 
            CHANGE tags tags LONGTEXT NOT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            ALTER TABLE claro_widget_container_config CHANGE layout layout LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_connection_message_slide CHANGE shortcuts shortcuts LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro_data_source CHANGE context context LONGTEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE tags tags LONGTEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro_log CHANGE details details LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro_menu_action CHANGE scope scope LONGTEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE api api LONGTEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro_resource_evaluation CHANGE more_data more_data LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro_resource_node CHANGE accesses accesses LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro_resource_type CHANGE tags tags LONGTEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro_widget CHANGE sources sources LONGTEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE context context LONGTEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE tags tags LONGTEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro_widget_container_config CHANGE layout layout LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_options CHANGE details details LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)", 
            CHANGE breadcrumbItems breadcrumbItems LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro_workspace_shortcuts CHANGE shortcuts_data shortcuts_data LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
    }
}
