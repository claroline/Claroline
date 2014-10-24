<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/21 03:31:16
 */
class Version20141021153113 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_536FFC4C5E237E06 ON claro_workspace_model (name) 
            WHERE name IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
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