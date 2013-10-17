<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

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
            ADD result NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD resultComparison SMALLINT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP COLUMN result
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP COLUMN resultComparison
        ");
    }
}