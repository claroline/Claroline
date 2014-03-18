<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/17 05:13:36
 */
class Version20140317171333 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            ADD COLUMN public_url VARCHAR(255) DEFAULT NULL 
            ADD COLUMN has_tuned_public_url SMALLINT NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852181F3A64 ON claro_user (public_url)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN public_url 
            DROP COLUMN has_tuned_public_url
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D2852181F3A64
        ");
    }
}