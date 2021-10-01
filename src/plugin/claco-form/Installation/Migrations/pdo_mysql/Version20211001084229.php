<?php

namespace Claroline\ClacoFormBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/10/01 08:42:31
 */
class Version20211001084229 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_clacoformbundle_field_choice_category 
            CHANGE field_value stringValue LONGTEXT DEFAULT NULL, 
            CHANGE array_value arrayValue LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
            CHANGE float_value floatValue DOUBLE PRECISION DEFAULT NULL, 
            CHANGE date_value dateValue DATETIME DEFAULT NULL, 
            CHANGE bool_value boolValue TINYINT(1) DEFAULT NULL
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE claro_clacoformbundle_field_choice_category 
            CHANGE stringValue field_value VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, 
            CHANGE arrayValue array_value LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci` COMMENT '(DC2Type:json_array)', 
            CHANGE floatvalue float_value DOUBLE PRECISION DEFAULT NULL, 
            CHANGE datevalue date_value DATETIME DEFAULT NULL, 
            CHANGE boolvalue bool_value TINYINT(1) DEFAULT NULL
        ");
    }
}
