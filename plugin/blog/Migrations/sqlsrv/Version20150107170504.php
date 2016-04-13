<?php

namespace Icap\BlogBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/01/07 05:05:06
 */
class Version20150107170504 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_widget_list_blog 
            DROP CONSTRAINT FK_294D4E02DAE07E97
        ');
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_294D4E02DAE07E97'
            ) 
            ALTER TABLE icap__blog_widget_list_blog 
            DROP CONSTRAINT IDX_294D4E02DAE07E97 ELSE 
            DROP INDEX IDX_294D4E02DAE07E97 ON icap__blog_widget_list_blog
        ");
        $this->addSql("
            sp_RENAME 'icap__blog_widget_list_blog.blog_id', 
            'resourceNode_id', 
            'COLUMN'
        ");
        $this->addSql('
            ALTER TABLE icap__blog_widget_list_blog 
            ADD CONSTRAINT FK_294D4E02B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_294D4E02B87FAB32 ON icap__blog_widget_list_blog (resourceNode_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_widget_list_blog 
            DROP CONSTRAINT FK_294D4E02B87FAB32
        ');
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_294D4E02B87FAB32'
            ) 
            ALTER TABLE icap__blog_widget_list_blog 
            DROP CONSTRAINT IDX_294D4E02B87FAB32 ELSE 
            DROP INDEX IDX_294D4E02B87FAB32 ON icap__blog_widget_list_blog
        ");
        $this->addSql("
            sp_RENAME 'icap__blog_widget_list_blog.resourcenode_id', 
            'blog_id', 
            'COLUMN'
        ");
        $this->addSql('
            ALTER TABLE icap__blog_widget_list_blog 
            ADD CONSTRAINT FK_294D4E02DAE07E97 FOREIGN KEY (blog_id) 
            REFERENCES icap__blog (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            CREATE INDEX IDX_294D4E02DAE07E97 ON icap__blog_widget_list_blog (blog_id)
        ');
    }
}
