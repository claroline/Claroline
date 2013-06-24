<?php

namespace Foo;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information : modify it with caution
 */
class Version1372086580 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_theme CHANGE plugin_id plugin_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_desktop_widget_config 
            DROP FOREIGN KEY FK_4AE48D62A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_log_desktop_widget_config 
            ADD CONSTRAINT FK_4AE48D62A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP FOREIGN KEY FK_D301C70782D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD CONSTRAINT FK_D301C70782D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            ALTER TABLE claro_log_hidden_workspace_widget_config 
            DROP FOREIGN KEY FK_BC83196EA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_log_hidden_workspace_widget_config 
            ADD CONSTRAINT FK_BC83196EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD CONSTRAINT FK_D9028545727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545727ACA70 ON claro_workspace (parent_id)
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP INDEX IDX_EB8D285282D40A1F, 
            ADD UNIQUE INDEX UNIQ_EB8D285282D40A1F (workspace_id)
        ");
        $this->addSql("
            ALTER TABLE claro_user CHANGE workspace_id workspace_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_message 
            ADD CONSTRAINT FK_D6FE8DD8727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_message (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_D6FE8DD8727ACA70 ON claro_message (parent_id)
        ");
        $this->addSql("
            ALTER TABLE claro_event CHANGE workspace_id workspace_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_license CHANGE acronym acronym VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            DROP FOREIGN KEY FK_DCF37C7E81C06096
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity CHANGE activity_id activity_id INT NOT NULL, 
            CHANGE sequence_order sequence_order VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            ADD CONSTRAINT FK_DCF37C7E81C06096 FOREIGN KEY (activity_id) 
            REFERENCES claro_activity (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource CHANGE path path VARCHAR(3000) NOT NULL, 
            CHANGE lvl lvl INT NOT NULL, 
            CHANGE mime_type mime_type VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_link CHANGE id id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            ADD CONSTRAINT FK_50B267EABF396750 FOREIGN KEY (id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_directory CHANGE id id INT NOT NULL
        ");
        $this->addSql("
            DROP INDEX UNIQ_AEC626935E237E06 ON claro_resource_type
        ");
        $this->addSql("
            ALTER TABLE claro_resource_icon 
            DROP INDEX IDX_478C58616773D783, 
            ADD UNIQUE INDEX UNIQ_478C58616773D783 (icon_type_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_icon 
            DROP INDEX IDX_478C586179F0D498, 
            ADD UNIQUE INDEX UNIQ_478C586179F0D498 (shortcut_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_icon CHANGE icon_type_id icon_type_id INT NOT NULL
        ");
        $this->addSql("
            DROP INDEX UNIQ_EA81C80BE1F029B6 ON claro_file
        ");
        $this->addSql("
            ALTER TABLE claro_file CHANGE id id INT NOT NULL, 
            CHANGE hash_name hash_name VARCHAR(36) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_text_revision CHANGE user_id user_id INT NOT NULL, 
            CHANGE content content VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_text_revision 
            ADD CONSTRAINT FK_F61948DE698D3548 FOREIGN KEY (text_id) 
            REFERENCES claro_text (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_F61948DE698D3548 ON claro_text_revision (text_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_icon_type CHANGE type type VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type_custom_action CHANGE resource_type_id resource_type_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut CHANGE id id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity CHANGE id id INT NOT NULL, 
            CHANGE instruction instruction VARCHAR(255) NOT NULL, 
            CHANGE start_date start_date DATETIME NOT NULL, 
            CHANGE end_date end_date DATETIME NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_text CHANGE id id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_role 
            DROP FOREIGN KEY FK_3177747182D40A1F
        ");
        $this->addSql("
            DROP INDEX UNIQ_317774715E237E06 ON claro_role
        ");
        $this->addSql("
            ALTER TABLE claro_role CHANGE name name VARCHAR(50) NOT NULL, 
            CHANGE translation_key translation_key VARCHAR(255) NOT NULL, 
            CHANGE type type INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_role 
            ADD CONSTRAINT FK_3177747182D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            ALTER TABLE claro_tools 
            DROP INDEX IDX_60F90965EC942BCF, 
            ADD UNIQUE INDEX UNIQ_60F90965EC942BCF (plugin_id)
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display CHANGE widget_id widget_id INT NOT NULL, 
            CHANGE is_locked is_locked TINYINT(1) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            ADD CONSTRAINT FK_2D34DB3727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_widget_display (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_2D34DB3727ACA70 ON claro_widget_display (parent_id)
        ");
        $this->addSql("
            ALTER TABLE claro_user_message CHANGE message_id message_id INT DEFAULT NULL, 
            CHANGE user_id user_id INT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity CHANGE id id INT AUTO_INCREMENT NOT NULL, 
            CHANGE instruction instruction VARCHAR(2055) NOT NULL, 
            CHANGE start_date start_date DATETIME DEFAULT NULL, 
            CHANGE end_date end_date DATETIME DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_directory CHANGE id id INT AUTO_INCREMENT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_event CHANGE workspace_id workspace_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_file CHANGE id id INT AUTO_INCREMENT NOT NULL, 
            CHANGE hash_name hash_name VARCHAR(50) NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EA81C80BE1F029B6 ON claro_file (hash_name)
        ");
        $this->addSql("
            ALTER TABLE claro_license CHANGE acronym acronym VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_link 
            DROP FOREIGN KEY FK_50B267EABF396750
        ");
        $this->addSql("
            ALTER TABLE claro_link CHANGE id id INT AUTO_INCREMENT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_desktop_widget_config 
            DROP FOREIGN KEY FK_4AE48D62A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_log_desktop_widget_config 
            ADD CONSTRAINT FK_4AE48D62A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_log_hidden_workspace_widget_config 
            DROP FOREIGN KEY FK_BC83196EA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_log_hidden_workspace_widget_config 
            ADD CONSTRAINT FK_BC83196EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            DROP FOREIGN KEY FK_D301C70782D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config 
            ADD CONSTRAINT FK_D301C70782D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_message 
            DROP FOREIGN KEY FK_D6FE8DD8727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_D6FE8DD8727ACA70 ON claro_message
        ");
        $this->addSql("
            ALTER TABLE claro_resource CHANGE lvl lvl INT DEFAULT NULL, 
            CHANGE path path VARCHAR(3000) DEFAULT NULL, 
            CHANGE mime_type mime_type VARCHAR(100) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            DROP FOREIGN KEY FK_DCF37C7E81C06096
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity CHANGE activity_id activity_id INT DEFAULT NULL, 
            CHANGE sequence_order sequence_order INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity 
            ADD CONSTRAINT FK_DCF37C7E81C06096 FOREIGN KEY (activity_id) 
            REFERENCES claro_resource (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_icon 
            DROP INDEX UNIQ_478C58616773D783, 
            ADD INDEX IDX_478C58616773D783 (icon_type_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_icon 
            DROP INDEX UNIQ_478C586179F0D498, 
            ADD INDEX IDX_478C586179F0D498 (shortcut_id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_icon CHANGE icon_type_id icon_type_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_icon_type CHANGE type type LONGTEXT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut CHANGE id id INT AUTO_INCREMENT NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_AEC626935E237E06 ON claro_resource_type (name)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type_custom_action CHANGE resource_type_id resource_type_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_role 
            DROP FOREIGN KEY FK_3177747182D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_role CHANGE name name VARCHAR(255) NOT NULL, 
            CHANGE translation_key translation_key VARCHAR(255) DEFAULT NULL, 
            CHANGE type type INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_role 
            ADD CONSTRAINT FK_3177747182D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_317774715E237E06 ON claro_role (name)
        ");
        $this->addSql("
            ALTER TABLE claro_text CHANGE id id INT AUTO_INCREMENT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_text_revision 
            DROP FOREIGN KEY FK_F61948DE698D3548
        ");
        $this->addSql("
            DROP INDEX IDX_F61948DE698D3548 ON claro_text_revision
        ");
        $this->addSql("
            ALTER TABLE claro_text_revision CHANGE user_id user_id INT DEFAULT NULL, 
            CHANGE content content LONGTEXT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_theme CHANGE plugin_id plugin_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_tools 
            DROP INDEX UNIQ_60F90965EC942BCF, 
            ADD INDEX IDX_60F90965EC942BCF (plugin_id)
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP INDEX UNIQ_EB8D285282D40A1F, 
            ADD INDEX IDX_EB8D285282D40A1F (workspace_id)
        ");
        $this->addSql("
            ALTER TABLE claro_user CHANGE workspace_id workspace_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_message CHANGE user_id user_id INT NOT NULL, 
            CHANGE message_id message_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display 
            DROP FOREIGN KEY FK_2D34DB3727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_2D34DB3727ACA70 ON claro_widget_display
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display CHANGE widget_id widget_id INT DEFAULT NULL, 
            CHANGE is_locked is_locked TINYINT(1) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP FOREIGN KEY FK_D9028545727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_D9028545727ACA70 ON claro_workspace
        ");
    }
}