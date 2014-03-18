<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/17 05:13:38
 */
class Version20140317171333 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            ADD public_url NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD has_tuned_public_url BIT NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852181F3A64 ON claro_user (public_url) 
            WHERE public_url IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN public_url
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN has_tuned_public_url
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_EB8D2852181F3A64'
            ) 
            ALTER TABLE claro_user 
            DROP CONSTRAINT UNIQ_EB8D2852181F3A64 ELSE 
            DROP INDEX UNIQ_EB8D2852181F3A64 ON claro_user
        ");
    }
}