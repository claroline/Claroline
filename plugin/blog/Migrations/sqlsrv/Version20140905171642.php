<?php

namespace Icap\BlogBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/09/05 05:16:44
 */
class Version20140905171642 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_post 
            ADD viewCounter INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__blog_options 
            ADD display_post_view_counter BIT NOT NULL
        ');
        $this->addSql("
            ALTER TABLE icap__blog_options 
            ADD CONSTRAINT DF_D1AAC984_59FD8A33 DEFAULT '1' FOR display_post_view_counter
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_options 
            DROP COLUMN display_post_view_counter
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post 
            DROP COLUMN viewCounter
        ');
    }
}
