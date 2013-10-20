<?php

namespace Claroline\CoreBundle\Migrations\oci8;

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
            ADD (
                result VARCHAR2(255) DEFAULT NULL, 
                resultComparison NUMBER(5) DEFAULT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP (result, resultComparison)
        ");
    }
}