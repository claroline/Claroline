<?php

namespace Claroline\RssReaderBundle\Migrations\pdo_pgsql;

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
            ADD widgetInstance_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            DROP user_id
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            DROP workspace_id
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            DROP is_default
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            DROP is_desktop
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            DROP CONSTRAINT FK_8D6D1C54A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            DROP CONSTRAINT FK_8D6D1C5482D40A1F
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
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE INDEX IDX_8D6D1C54AB7B5A55 ON claro_rssreader_configuration (widgetInstance_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            ADD workspace_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            ADD is_default BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            ADD is_desktop BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration RENAME COLUMN widgetinstance_id TO user_id
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            DROP CONSTRAINT FK_8D6D1C54AB7B5A55
        ");
        $this->addSql("
            DROP INDEX IDX_8D6D1C54AB7B5A55
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            ADD CONSTRAINT FK_8D6D1C54A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_rssreader_configuration 
            ADD CONSTRAINT FK_8D6D1C5482D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE INDEX IDX_8D6D1C5482D40A1F ON claro_rssreader_configuration (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8D6D1C54A76ED395 ON claro_rssreader_configuration (user_id)
        ");
    }
}