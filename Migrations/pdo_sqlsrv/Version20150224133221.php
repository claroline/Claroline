<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/24 01:32:24
 */
class Version20150224133221 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_plugin 
            DROP COLUMN icon
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            DROP COLUMN icon
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_plugin 
            ADD icon NVARCHAR(255) COLLATE utf8_unicode_ci NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget 
            ADD icon NVARCHAR(255) COLLATE utf8_unicode_ci NOT NULL
        ");
    }
}