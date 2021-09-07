<?php

namespace Icap\BlogBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/03/13 09:20:45
 */
class Version20200924102700 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DROP TABLE IF EXISTS icap__blog_widget_blog
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
