<?php

namespace ICAP\BlogBundle\Migrations;

use Claroline\CoreBundle\Library\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20130530152255 extends BundleMigration
{
    /**
     * Will be fired at the plugin installation.
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema)
    {
        $this
            ->createBlogTable($schema)
            ->createBlogPostTable($schema)
            ->createBlogCommentTable($schema)
        ;
    }

    /**
     * Will be fired at the plugin uninstallation.
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema
            ->dropTable('icap__blog_comment')
            ->dropTable('icap__blog_post')
            ->dropTable('icap__blog')
        ;
         //@TODO Remove associatedTag with posts
    }

    /**
     * Create the 'icap_blog' table.
     *
     * @param Schema $schema
     * @return Version20130530152255
     */
    private function createBlogTable(Schema $schema)
    {
        $table = $schema->createTable('icap__blog');
        $this->addId($table);

        $this->storeTable($table);

        return $this;
    }

    /**
     * Create the 'icap_blog_post' table.
     *
     * @param Schema $schema
     * @return Version20130530152255
     */
    private function createBlogPostTable(Schema $schema)
    {
        $table = $schema->createTable('icap__blog_post');
        $this->addId($table);
        $table->addColumn('title', 'string');
        $table->addColumn('content', 'text');
        $table->addColumn('status', 'integer');
        $table->addColumn('slug', 'string', array('length' => 128));
        $table->addColumn('creation_date', 'datetime');
        $table->addColumn('modification_date', 'datetime');
        $table->addColumn('publication_date', 'datetime');
        $table->addColumn('user_id', 'integer', array('notnull' => true));
        $table->addColumn('blog_id', 'integer', array('notnull' => true));

        $table->addUniqueIndex(array('slug'));

        $table->addForeignKeyConstraint(
            $schema->getTable('claro_user'),
            array('user_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('icap__blog'),
            array('blog_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );

        $this->storeTable($table);

        return $this;
    }

    /**
     * Create the 'icap_blog_comment' table.
     *
     * @param Schema $schema
     * @return Version20130530152255
     */
    private function createBlogCommentTable(Schema $schema)
    {
        $table = $schema->createTable('icap__blog_comment');
        $this->addId($table);
        $table->addColumn('message', 'text');
        $table->addColumn('creation_date', 'datetime');
        $table->addColumn('user_id', 'integer', array('notnull' => true));
        $table->addColumn('post_id', 'integer', array('notnull' => true));

        $table->addForeignKeyConstraint(
            $schema->getTable('claro_user'),
            array('user_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $table->addForeignKeyConstraint(
            $this->getStoredTable('icap__blog_post'),
            array('post_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );

        $this->storeTable($table);

        return $this;
    }
}
