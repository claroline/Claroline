<?php

namespace Icap\BlogBundle\Migrations\pdo_oci;

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
            ALTER TABLE icap__blog_widget_list_blog 
            DROP CONSTRAINT FK_294D4E02DAE07E97
        ");
        $this->addSql("
            DROP INDEX IDX_294D4E02DAE07E97
        ");
        $this->addSql("
            ALTER TABLE icap__blog_widget_list_blog RENAME COLUMN blog_id TO resourceNode_id
        ");
        $this->addSql("
            ALTER TABLE icap__blog_widget_list_blog 
            ADD CONSTRAINT FK_294D4E02B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_294D4E02B87FAB32 ON icap__blog_widget_list_blog (resourceNode_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__blog_widget_list_blog 
            DROP CONSTRAINT FK_294D4E02B87FAB32
        ");
        $this->addSql("
            DROP INDEX IDX_294D4E02B87FAB32
        ");
        $this->addSql("
            ALTER TABLE icap__blog_widget_list_blog RENAME COLUMN resourcenode_id TO blog_id
        ");
        $this->addSql("
            ALTER TABLE icap__blog_widget_list_blog 
            ADD CONSTRAINT FK_294D4E02DAE07E97 FOREIGN KEY (blog_id) 
            REFERENCES icap__blog (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_294D4E02DAE07E97 ON icap__blog_widget_list_blog (blog_id)
        ");
    }
}