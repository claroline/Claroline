<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/03/17 05:13:35
 */
class Version20140317171333 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            ADD public_url VARCHAR(255) DEFAULT NULL, 
            ADD has_tuned_public_url TINYINT(1) NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852181F3A64 ON claro_user (public_url)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_EB8D2852181F3A64 ON claro_user
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            DROP public_url, 
            DROP has_tuned_public_url
        ");
    }
}