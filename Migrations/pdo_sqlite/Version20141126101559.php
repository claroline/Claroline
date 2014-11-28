<?php

namespace Icap\BlogBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/26 10:16:01
 */
class Version20141126101559 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__blog_widget_list (
                id INTEGER NOT NULL, 
                blog_id INTEGER DEFAULT NULL, 
                widgetInstance_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_6979A1C3DAE07E97 ON icap__blog_widget_list (blog_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6979A1C3AB7B5A55 ON icap__blog_widget_list (widgetInstance_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE icap__blog_widget_list
        ");
    }
}