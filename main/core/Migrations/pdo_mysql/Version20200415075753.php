<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/04/15 07:58:11
 */
class Version20200415075753 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_user 
            ADD is_locked TINYINT(1) NOT NULL DEFAULT 0, 
            DROP last_uri
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_user 
            ADD last_uri VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
            DROP is_locked
        ');
    }
}
