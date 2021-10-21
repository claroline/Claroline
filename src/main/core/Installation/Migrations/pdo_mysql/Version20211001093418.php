<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/10/01 09:34:20
 */
class Version20211001093418 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_field_facet_value 
            ADD field_value LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');

        $this->addSql('
            UPDATE claro_field_facet_value SET field_value = CONCAT("\"", stringValue, "\"") WHERE stringValue IS NOT NULL
        ');

        $this->addSql('
            UPDATE claro_field_facet_value SET field_value = CONCAT("\"", REPLACE(dateValue, " ", "\\T"), "\"") WHERE dateValue IS NOT NULL
        ');

        $this->addSql('
            UPDATE claro_field_facet_value SET field_value = floatValue WHERE floatValue IS NOT NULL
        ');

        $this->addSql('
            UPDATE claro_field_facet_value SET field_value = arrayValue WHERE arrayValue IS NOT NULL
        ');

        $this->addSql('
            UPDATE claro_field_facet_value SET field_value = "true" WHERE boolValue IS NOT NULL AND boolValue = 1
        ');

        $this->addSql('
            UPDATE claro_field_facet_value SET field_value = "false" WHERE boolValue IS NOT NULL AND boolValue = 0
        ');

        $this->addSql('
            ALTER TABLE claro_field_facet_value 
            DROP stringValue, 
            DROP floatValue, 
            DROP dateValue, 
            DROP arrayValue, 
            DROP boolValue
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_field_facet_value 
            DROP field_value,
            ADD stringValue LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            ADD floatValue DOUBLE PRECISION DEFAULT NULL, 
            ADD dateValue DATETIME DEFAULT NULL, 
            ADD arrayValue LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT "(DC2Type:json)", 
            ADD boolValue TINYINT(1) DEFAULT NULL
        ');
    }
}
