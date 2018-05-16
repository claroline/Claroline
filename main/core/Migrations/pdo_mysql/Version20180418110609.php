<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/04/18 11:06:11
 */
class Version20180418110609 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_field_facet
            ADD hidden TINYINT(1) DEFAULT '0' NOT NULL,
            ADD is_metadata TINYINT(1) DEFAULT '0' NOT NULL,
            ADD locked TINYINT(1) DEFAULT '0' NOT NULL,
            ADD locked_edition TINYINT(1) DEFAULT '0' NOT NULL,
            ADD help VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql('
            ALTER TABLE claro_field_facet_value
            ADD boolValue TINYINT(1) DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_field_facet
            DROP hidden,
            DROP is_metadata,
            DROP locked,
            DROP locked_edition,
            DROP help
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet_value
            DROP boolValue
        ');
    }
}
