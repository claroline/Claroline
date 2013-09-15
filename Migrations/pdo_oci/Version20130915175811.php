<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/15 05:58:13
 */
class Version20130915175811 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log_desktop_widget_config 
            DROP CONSTRAINT FK_4AE48D62A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_log_desktop_widget_config 
            ADD CONSTRAINT FK_4AE48D62A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_log_hidden_workspace_widget_config 
            DROP CONSTRAINT FK_BC83196EA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_log_hidden_workspace_widget_config 
            ADD CONSTRAINT FK_BC83196EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE simple_text_dekstop_widget_config 
            DROP CONSTRAINT FK_BAB9695A76ED395
        ");
        $this->addSql("
            ALTER TABLE simple_text_dekstop_widget_config 
            ADD CONSTRAINT FK_BAB9695A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            DROP CONSTRAINT FK_11925ED382D40A1F
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            ADD CONSTRAINT FK_11925ED382D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_log_desktop_widget_config 
            DROP CONSTRAINT FK_4AE48D62A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_log_desktop_widget_config 
            ADD CONSTRAINT FK_4AE48D62A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_log_hidden_workspace_widget_config 
            DROP CONSTRAINT FK_BC83196EA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_log_hidden_workspace_widget_config 
            ADD CONSTRAINT FK_BC83196EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE simple_text_dekstop_widget_config 
            DROP CONSTRAINT FK_BAB9695A76ED395
        ");
        $this->addSql("
            ALTER TABLE simple_text_dekstop_widget_config 
            ADD CONSTRAINT FK_BAB9695A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            DROP CONSTRAINT FK_11925ED382D40A1F
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            ADD CONSTRAINT FK_11925ED382D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ");
    }
}