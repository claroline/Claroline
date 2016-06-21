<?php

namespace Icap\BlogBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/09/16 04:34:57
 */
class Version20150916163456 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_options 
            ADD tag_top_mode TINYINT(1) NOT NULL, 
            ADD max_tag SMALLINT DEFAULT 50 NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_options 
            DROP tag_top_mode, 
            DROP max_tag
        ');
    }
}
