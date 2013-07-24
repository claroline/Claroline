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
            ->createBlogOptionsTable($schema)
            ->createBlogPostTable($schema)
            ->createBlogCommentTable($schema)
            ->createTagTable($schema)
        ;
    }

    /**
     * Will be fired at the plugin uninstallation.
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        //Marche pas et aucune idée de pourquoi, merci le migrationBundle
        $this->addSql("DELETE FROM `icap__associated_tag` WHERE `taggableClass` = 'ICAP\\BlogBundle\\Entity\\Post'");

        $schema
            ->dropTable('icap__blog_comment')
            ->dropTable('icap__blog_post')
            ->dropTable('icap__blog_options')
            ->dropTable('icap__blog')
        ;
    }

    /**
     * Create the 'icap__blog' table.
     *
     * @param Schema $schema
     * @return Version20130530152255
     */
    private function createBlogTable(Schema $schema)
    {
        $table = $schema->createTable('icap__blog');
        $this->addId($table);
        $table->addColumn('infos', 'text');

        $this->storeTable($table);

        return $this;
    }

    /**
     * Create the 'icap__blog_options' table.
     *
     * @param Schema $schema
     * @return Version20130530152255
     */
    private function createBlogOptionsTable(Schema $schema)
    {
        $table = $schema->createTable('icap__blog_options');
        $this->addId($table);
        $table->addColumn('blog_id', 'integer');
        $table->addColumn('authorize_comment', 'boolean');
        $table->addColumn('authorize_anonymous_comment', 'boolean');
        $table->addColumn('post_per_page', 'smallint');
        $table->addColumn('auto_publish_post', 'boolean');
        $table->addColumn('auto_publish_comment', 'boolean');

        $table->addUniqueIndex(array('blog_id'));

        $table->addForeignKeyConstraint(
            $schema->getTable('icap__blog'),
            array('blog_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );

        $this->storeTable($table);

        return $this;
    }

    /**
     * Create the 'icap__blog_post' table.
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
        $table->addColumn('publication_date', 'datetime', array('notnull' => false));
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
     * Create the 'icap__blog_comment' table.
     *
     * @param Schema $schema
     * @return Version20130530152255
     */
    private function createBlogCommentTable(Schema $schema)
    {
        $table = $schema->createTable('icap__blog_comment');
        $this->addId($table);
        $table->addColumn('message', 'text');
        $table->addColumn('status', 'integer');
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

    /**
     * Create the 'icap__blog_tag' table.
     *
     * @param \Doctrine\DBAL\Schema\Schema $schema
     *
     * @return Version20130530152255
     */
    private function createTagTable(Schema $schema)
    {
        $tagTable = $schema->createTable('icap__blog_tag');
        $this->addId($tagTable);
        $tagTable->addColumn('name', 'string', array('length' => 255));

        $tagTable->addUniqueIndex(array('name'));

        $this->storeTable($tagTable);

        $associatedTagTable = $schema->createTable('icap__blog_post_tag');
        $associatedTagTable->addColumn('post_id', 'integer', array('notnull' => true));
        $associatedTagTable->addColumn('tag_id', 'integer', array('notnull' => true));

        $associatedTagTable->setPrimaryKey(array('post_id', 'tag_id'));

        $associatedTagTable->addForeignKeyConstraint(
            $this->getStoredTable('icap__blog_post'),
            array('post_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
        $associatedTagTable->addForeignKeyConstraint(
            $schema->getTable('icap__blog_tag'),
            array('tag_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );

        $this->storeTable($associatedTagTable);
    }
}
