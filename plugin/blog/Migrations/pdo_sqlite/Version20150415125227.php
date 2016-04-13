<?php

namespace Icap\BlogBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/04/15 12:52:28
 */
class Version20150415125227 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE icap__blog_widget_tag_list_blog (
                id INTEGER NOT NULL, 
                tag_cloud INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                widgetInstance_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('
            CREATE INDEX IDX_75E7D178B87FAB32 ON icap__blog_widget_tag_list_blog (resourceNode_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_75E7D178AB7B5A55 ON icap__blog_widget_tag_list_blog (widgetInstance_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX unique_widget_tag_list_blog ON icap__blog_widget_tag_list_blog (
                resourceNode_id, widgetInstance_id
            )
        ');
        $this->addSql('
            DROP INDEX IDX_EDA40898B87FAB32
        ');
        $this->addSql('
            DROP INDEX IDX_EDA40898AB7B5A55
        ');
        $this->addSql('
            CREATE TEMPORARY TABLE __temp__icap__blog_widget_blog AS 
            SELECT id, 
            resourceNode_id, 
            widgetInstance_id 
            FROM icap__blog_widget_blog
        ');
        $this->addSql('
            DROP TABLE icap__blog_widget_blog
        ');
        $this->addSql('
            CREATE TABLE icap__blog_widget_blog (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                widgetInstance_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EDA40898AB7B5A55 FOREIGN KEY (widgetInstance_id) 
                REFERENCES claro_widget_instance (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_EDA40898B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ');
        $this->addSql('
            INSERT INTO icap__blog_widget_blog (
                id, resourceNode_id, widgetInstance_id
            ) 
            SELECT id, 
            resourceNode_id, 
            widgetInstance_id 
            FROM __temp__icap__blog_widget_blog
        ');
        $this->addSql('
            DROP TABLE __temp__icap__blog_widget_blog
        ');
        $this->addSql('
            CREATE INDEX IDX_EDA40898B87FAB32 ON icap__blog_widget_blog (resourceNode_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_EDA40898AB7B5A55 ON icap__blog_widget_blog (widgetInstance_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX unique_widget_blog ON icap__blog_widget_blog (
                resourceNode_id, widgetInstance_id
            )
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE icap__blog_widget_tag_list_blog
        ');
        $this->addSql('
            DROP INDEX IDX_EDA40898B87FAB32
        ');
        $this->addSql('
            DROP INDEX IDX_EDA40898AB7B5A55
        ');
        $this->addSql('
            DROP INDEX unique_widget_blog
        ');
        $this->addSql('
            CREATE TEMPORARY TABLE __temp__icap__blog_widget_blog AS 
            SELECT id, 
            resourceNode_id, 
            widgetInstance_id 
            FROM icap__blog_widget_blog
        ');
        $this->addSql('
            DROP TABLE icap__blog_widget_blog
        ');
        $this->addSql('
            CREATE TABLE icap__blog_widget_blog (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                widgetInstance_id INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EDA40898B87FAB32 FOREIGN KEY (resourceNode_id) 
                REFERENCES claro_resource_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_EDA40898AB7B5A55 FOREIGN KEY (widgetInstance_id) 
                REFERENCES claro_widget_instance (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ');
        $this->addSql('
            INSERT INTO icap__blog_widget_blog (
                id, resourceNode_id, widgetInstance_id
            ) 
            SELECT id, 
            resourceNode_id, 
            widgetInstance_id 
            FROM __temp__icap__blog_widget_blog
        ');
        $this->addSql('
            DROP TABLE __temp__icap__blog_widget_blog
        ');
        $this->addSql('
            CREATE INDEX IDX_EDA40898B87FAB32 ON icap__blog_widget_blog (resourceNode_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_EDA40898AB7B5A55 ON icap__blog_widget_blog (widgetInstance_id)
        ');
    }
}
