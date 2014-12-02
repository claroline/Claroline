<?php

namespace Icap\BlogBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/12/01 04:41:21
 */
class Version20141201164119 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__blog_widget_list_blog (
                id INTEGER NOT NULL, 
                blog_id INTEGER NOT NULL, 
                widgetInstance_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_294D4E02DAE07E97 ON icap__blog_widget_list_blog (blog_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_294D4E02AB7B5A55 ON icap__blog_widget_list_blog (widgetInstance_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__blog_widget_list_blog
        ");
    }
}