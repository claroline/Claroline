<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/18 11:35:04
 */
class Version20140618113503 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            ADD COLUMN who VARCHAR(255) DEFAULT NULL 
            ADD COLUMN \"where\" VARCHAR(255) DEFAULT NULL 
            ADD COLUMN with_tutor SMALLINT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            DROP COLUMN who 
            DROP COLUMN \"where\" 
            DROP COLUMN with_tutor
        ");
    }
}