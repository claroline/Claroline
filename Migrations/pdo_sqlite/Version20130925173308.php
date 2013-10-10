<?php

namespace Claroline\RssReaderBundle\Migrations\pdo_sqlite;

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
            DROP INDEX IDX_8D6D1C5482D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_8D6D1C54A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_rssreader_configuration AS 
            SELECT id, 
            url 
            FROM claro_rssreader_configuration
        ");
        $this->addSql("
            DROP TABLE claro_rssreader_configuration
        ");
        $this->addSql("
            CREATE TABLE claro_rssreader_configuration (
                id INTEGER NOT NULL, 
                url VARCHAR(255) NOT NULL, 
                widgetInstance_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_8D6D1C54AB7B5A55 FOREIGN KEY (widgetInstance_id) 
                REFERENCES claro_widget_instance (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_rssreader_configuration (id, url) 
            SELECT id, 
            url 
            FROM __temp__claro_rssreader_configuration
        ");
        $this->addSql("
            DROP TABLE __temp__claro_rssreader_configuration
        ");
        $this->addSql("
            CREATE INDEX IDX_8D6D1C54AB7B5A55 ON claro_rssreader_configuration (widgetInstance_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_8D6D1C54AB7B5A55
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_rssreader_configuration AS 
            SELECT id, 
            url, 
            widgetInstance_id 
            FROM claro_rssreader_configuration
        ");
        $this->addSql("
            DROP TABLE claro_rssreader_configuration
        ");
        $this->addSql("
            CREATE TABLE claro_rssreader_configuration (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                url VARCHAR(255) NOT NULL, 
                is_default BOOLEAN NOT NULL, 
                is_desktop BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_8D6D1C54A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_8D6D1C5482D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_rssreader_configuration (id, url, user_id) 
            SELECT id, 
            url, 
            widgetInstance_id 
            FROM __temp__claro_rssreader_configuration
        ");
        $this->addSql("
            DROP TABLE __temp__claro_rssreader_configuration
        ");
        $this->addSql("
            CREATE INDEX IDX_8D6D1C5482D40A1F ON claro_rssreader_configuration (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8D6D1C54A76ED395 ON claro_rssreader_configuration (user_id)
        ");
    }
}