<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/23 10:37:36
 */
class Version20150223103734 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_bundle 
            ADD type NVARCHAR(50) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_bundle 
            ADD authors VARCHAR(MAX) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_bundle 
            ADD description VARCHAR(MAX)
        ");
        $this->addSql("
            ALTER TABLE claro_bundle 
            ADD license VARCHAR(MAX) NOT NULL
        ");
        $this->addSql("
            EXEC sp_addextendedproperty N 'MS_Description', 
            N '(DC2Type:json_array)', 
            N 'SCHEMA', 
            dbo, 
            N 'TABLE', 
            claro_bundle, 
            N 'COLUMN', 
            authors
        ");
        $this->addSql("
            EXEC sp_addextendedproperty N 'MS_Description', 
            N '(DC2Type:json_array)', 
            N 'SCHEMA', 
            dbo, 
            N 'TABLE', 
            claro_bundle, 
            N 'COLUMN', 
            license
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_bundle 
            DROP COLUMN type
        ");
        $this->addSql("
            ALTER TABLE claro_bundle 
            DROP COLUMN authors
        ");
        $this->addSql("
            ALTER TABLE claro_bundle 
            DROP COLUMN description
        ");
        $this->addSql("
            ALTER TABLE claro_bundle 
            DROP COLUMN license
        ");
    }
}