<?php

namespace Icap\BlogBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/01/07 05:05:05
 */
class Version20150107170504 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_294D4E02AB7B5A55
        ");
        $this->addSql("
            DROP INDEX IDX_294D4E02DAE07E97
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__blog_widget_list_blog AS 
            SELECT id, 
            blog_id, 
            widgetInstance_id 
            FROM icap__blog_widget_list_blog
        ");
        $this->addSql("
            DROP TABLE icap__blog_widget_list_blog
        ");
        $this->addSql("
            CREATE TABLE icap__blog_widget_list_blog (
                id INTEGER NOT NULL, 
                widgetInstance_id INTEGER NOT NULL, 
                resourceNode_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_294D4E02AB7B5A55 FOREIGN KEY (widgetInstance_id) 
                REFERENCES claro_widget_instance (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_294D4E02B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__blog_widget_list_blog (
                id, resourceNode_id, widgetInstance_id
            ) 
            SELECT id, 
            blog_id, 
            widgetInstance_id 
            FROM __temp__icap__blog_widget_list_blog
        ");
        $this->addSql("
            DROP TABLE __temp__icap__blog_widget_list_blog
        ");
        $this->addSql("
            CREATE INDEX IDX_294D4E02AB7B5A55 ON icap__blog_widget_list_blog (widgetInstance_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_294D4E02B87FAB32 ON icap__blog_widget_list_blog (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_294D4E02B87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_294D4E02AB7B5A55
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__icap__blog_widget_list_blog AS 
            SELECT id, 
            resourceNode_id, 
            widgetInstance_id 
            FROM icap__blog_widget_list_blog
        ");
        $this->addSql("
            DROP TABLE icap__blog_widget_list_blog
        ");
        $this->addSql("
            CREATE TABLE icap__blog_widget_list_blog (
                id INTEGER NOT NULL, 
                blog_id INTEGER NOT NULL, 
                widgetInstance_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_294D4E02AB7B5A55 FOREIGN KEY (widgetInstance_id) 
                REFERENCES claro_widget_instance (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_294D4E02DAE07E97 FOREIGN KEY (blog_id) 
                REFERENCES icap__blog (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO icap__blog_widget_list_blog (id, blog_id, widgetInstance_id) 
            SELECT id, 
            resourceNode_id, 
            widgetInstance_id 
            FROM __temp__icap__blog_widget_list_blog
        ");
        $this->addSql("
            DROP TABLE __temp__icap__blog_widget_list_blog
        ");
        $this->addSql("
            CREATE INDEX IDX_294D4E02AB7B5A55 ON icap__blog_widget_list_blog (widgetInstance_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_294D4E02DAE07E97 ON icap__blog_widget_list_blog (blog_id)
        ");
    }
}