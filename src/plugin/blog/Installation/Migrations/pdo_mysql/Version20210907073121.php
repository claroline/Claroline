<?php

namespace Icap\BlogBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/09/07 07:31:25
 */
class Version20210907073121 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__blog_options 
            DROP display_title, 
            DROP banner_activate
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post 
            ADD poster VARCHAR(255) DEFAULT NULL, 
            ADD thumbnail VARCHAR(255) DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE icap__blog_options 
            ADD display_title TINYINT(1) NOT NULL, 
            ADD banner_activate TINYINT(1) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE icap__blog_post 
            DROP poster, 
            DROP thumbnail
        ');
    }
}
