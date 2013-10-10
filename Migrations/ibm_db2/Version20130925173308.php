<?php

namespace Claroline\RssReaderBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/25 05:33:08
 */
class Version20130925173308 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            ADD COLUMN widgetInstance_id INTEGER DEFAULT NULL 
            DROP COLUMN user_id 
            DROP COLUMN workspace_id 
            DROP COLUMN is_default 
            DROP COLUMN is_desktop
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            DROP FOREIGN KEY FK_8D6D1C54A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            DROP FOREIGN KEY FK_8D6D1C5482D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_8D6D1C5482D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_8D6D1C54A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            ADD CONSTRAINT FK_8D6D1C54AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_8D6D1C54AB7B5A55 ON claro_rssreader_configuration (widgetInstance_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            ADD COLUMN workspace_id INTEGER DEFAULT NULL 
            ADD COLUMN is_default SMALLINT NOT NULL 
            ADD COLUMN is_desktop SMALLINT NOT NULL RENAME widgetinstance_id TO user_id
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            DROP FOREIGN KEY FK_8D6D1C54AB7B5A55
        ");
        $this->addSql("
            DROP INDEX IDX_8D6D1C54AB7B5A55
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            ADD CONSTRAINT FK_8D6D1C54A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            ADD CONSTRAINT FK_8D6D1C5482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8D6D1C5482D40A1F ON claro_rssreader_configuration (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8D6D1C54A76ED395 ON claro_rssreader_configuration (user_id)
        ");
    }
}