<?php

namespace Icap\WebsiteBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/07/04 04:03:26
 */
class Version20180704160323 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__website_page 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE icap__website_page
            SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_FB66D1D4D17F50A6 ON icap__website_page (uuid)
        ');
        $this->addSql('
            ALTER TABLE icap__website 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE icap__website
            SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_452309F8D17F50A6 ON icap__website (uuid)
        ');
        $this->addSql('
            ALTER TABLE icap__website_options 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE icap__website_options
            SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_C40F177D17F50A6 ON icap__website_options (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_452309F8D17F50A6 ON icap__website
        ');
        $this->addSql('
            ALTER TABLE icap__website 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_C40F177D17F50A6 ON icap__website_options
        ');
        $this->addSql('
            ALTER TABLE icap__website_options 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_FB66D1D4D17F50A6 ON icap__website_page
        ');
        $this->addSql('
            ALTER TABLE icap__website_page 
            DROP uuid
        ');
    }
}
