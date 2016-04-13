<?php

namespace Icap\BlogBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/02/20 03:06:34
 */
class Version20150220150632 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE icap__blog_widget_blog (
                id INTEGER NOT NULL, 
                resourceNode_id INTEGER DEFAULT NULL, 
                widgetInstance_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('
            CREATE INDEX IDX_EDA40898B87FAB32 ON icap__blog_widget_blog (resourceNode_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_EDA40898AB7B5A55 ON icap__blog_widget_blog (widgetInstance_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE icap__blog_widget_blog
        ');
    }
}
