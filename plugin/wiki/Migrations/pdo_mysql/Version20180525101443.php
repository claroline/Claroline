<?php

namespace Icap\WikiBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/05/25 10:14:44
 */
class Version20180525101443 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE icap__wiki_section
            SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_82904AAD17F50A6 ON icap__wiki_section (uuid)
        ');

        $this->addSql('
            ALTER TABLE icap__wiki_contribution 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE icap__wiki_contribution
            SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_781E6502D17F50A6 ON icap__wiki_contribution (uuid)
        ');

        $this->addSql('
            ALTER TABLE icap__wiki 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE icap__wiki
            SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_1FAD6B81D17F50A6 ON icap__wiki (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_1FAD6B81D17F50A6 ON icap__wiki
        ');
        $this->addSql('
            ALTER TABLE icap__wiki 
            DROP uuid
        ');

        $this->addSql('
            DROP INDEX UNIQ_781E6502D17F50A6 ON icap__wiki_contribution
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_contribution 
            DROP uuid
        ');

        $this->addSql('
            DROP INDEX UNIQ_82904AAD17F50A6 ON icap__wiki_section
        ');
        $this->addSql('
            ALTER TABLE icap__wiki_section 
            DROP uuid
        ');
    }
}
