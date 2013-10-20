<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/10/17 11:03:52
 */
class Version20131017110349 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD COLUMN \"result\" VARCHAR(255) DEFAULT NULL 
            ADD COLUMN resultComparison SMALLINT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP COLUMN \"result\" 
            DROP COLUMN resultComparison
        ");
    }
}