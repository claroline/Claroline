<?php

namespace Icap\BlogBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2014/09/08 03:47:50
 */
class Version20140908154749 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_post CHANGE modification_date modification_date DATETIME DEFAULT NULL, 
            CHANGE viewCounter viewCounter INT DEFAULT 0 NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__blog_post CHANGE modification_date modification_date DATETIME NOT NULL, 
            CHANGE viewCounter viewCounter INT NOT NULL
        ');
    }
}
