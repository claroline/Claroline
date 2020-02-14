<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/02/14 09:54:23
 */
class Version20200214095421 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_tools 
            DROP display_name, 
            DROP desktop_category
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_tools 
            ADD display_name VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
            ADD desktop_category VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci
        ');
    }
}
