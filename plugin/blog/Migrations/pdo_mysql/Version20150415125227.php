<?php

namespace Icap\BlogBundle\Migrations\pdo_mysql;

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
                id INT AUTO_INCREMENT NOT NULL, 
                tag_cloud SMALLINT NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                widgetInstance_id INT NOT NULL, 
                INDEX IDX_75E7D178B87FAB32 (resourceNode_id), 
                INDEX IDX_75E7D178AB7B5A55 (widgetInstance_id), 
                UNIQUE INDEX unique_widget_tag_list_blog (
                    resourceNode_id, widgetInstance_id
                ), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE icap__blog_widget_tag_list_blog 
            ADD CONSTRAINT FK_75E7D178B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            ALTER TABLE icap__blog_widget_tag_list_blog 
            ADD CONSTRAINT FK_75E7D178AB7B5A55 FOREIGN KEY (widgetInstance_id) 
            REFERENCES claro_widget_instance (id) 
            ON DELETE CASCADE
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
            DROP INDEX unique_widget_blog ON icap__blog_widget_blog
        ');
    }
}
