<?php

namespace Icap\PortfolioBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/06/10 11:43:47
 */
class Version20150610114345 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio 
            ADD title VARCHAR(128) NOT NULL, 
            ADD slug VARCHAR(128) DEFAULT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_8B1895D989D9B62 ON icap__portfolio (slug)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_8B1895D989D9B62 ON icap__portfolio
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio 
            DROP title, 
            DROP slug
        ");
    }
}