<?php

namespace Icap\BlogBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/01/06 04:37:11
 */
class Version20150106163709 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE INDEX IDX_FD75E6C4B87FAB32 ON icap__blog (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D1AAC984DAE07E97 ON icap__blog_options (blog_id)
        ");
        $this->addSql("
            sp_RENAME 'icap__blog_widget_list_blog.blog_id', 
            'resourceNode_id', 
            'COLUMN'
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
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_FD75E6C4B87FAB32'
            ) 
            ALTER TABLE icap__blog 
            DROP CONSTRAINT IDX_FD75E6C4B87FAB32 ELSE 
            DROP INDEX IDX_FD75E6C4B87FAB32 ON icap__blog
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_D1AAC984DAE07E97'
            ) 
            ALTER TABLE icap__blog_options 
            DROP CONSTRAINT IDX_D1AAC984DAE07E97 ELSE 
            DROP INDEX IDX_D1AAC984DAE07E97 ON icap__blog_options
        ");
        $this->addSql("
            ALTER TABLE icap__blog_widget_list_blog 
            DROP CONSTRAINT FK_294D4E02B87FAB32
        ");
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
    }
}