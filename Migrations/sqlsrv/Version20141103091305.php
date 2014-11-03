<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/03 09:13:08
 */
class Version20141103091305 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_536FFC4C5E237E06 ON claro_workspace_model (name) 
            WHERE name IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_type 
            ADD publish BIT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_type 
            DROP COLUMN publish
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_536FFC4C5E237E06'
            ) 
            ALTER TABLE claro_workspace_model 
            DROP CONSTRAINT UNIQ_536FFC4C5E237E06 ELSE 
            DROP INDEX UNIQ_536FFC4C5E237E06 ON claro_workspace_model
        ");
    }
}