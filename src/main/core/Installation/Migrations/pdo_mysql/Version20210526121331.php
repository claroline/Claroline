<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/05/26 12:13:49
 */
class Version20210526121331 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_field_facet_value CHANGE arrayValue arrayValue LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_field_facet_value CHANGE arrayValue arrayValue LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
    }
}
