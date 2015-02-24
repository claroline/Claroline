<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/24 01:32:22
 */
class Version20150224133221 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_plugin 
            DROP icon
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP icon
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_plugin 
            ADD icon VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD icon VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ");
    }
}