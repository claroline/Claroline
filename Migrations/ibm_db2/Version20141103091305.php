<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/03 09:13:07
 */
class Version20141103091305 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_536FFC4C5E237E06 ON claro_workspace_model (name)
        ");
        $this->addSql("
            ALTER TABLE claro_type 
            ADD COLUMN publish SMALLINT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_type 
            DROP COLUMN publish
        ");
        $this->addSql("
            DROP INDEX UNIQ_536FFC4C5E237E06
        ");
    }
}