<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/23 11:51:06
 */
class Version20130923115105 extends AbstractMigration
{
    public function up(Schema $schema)
    {
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
            REFERENCES claro_widget_display (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_11925ED3EF00646E ON simple_text_workspace_widget_config (displayConfig_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config 
            DROP FOREIGN KEY FK_11925ED3EF00646E
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