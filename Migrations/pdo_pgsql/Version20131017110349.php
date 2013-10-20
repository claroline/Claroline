<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/17 11:03:51
 */
class Version20131017110349 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD result VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD resultComparison SMALLINT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP result
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP resultComparison
        ");
    }
}