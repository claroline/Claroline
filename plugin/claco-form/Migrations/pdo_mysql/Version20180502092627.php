<?php

namespace Claroline\ClacoFormBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/05/02 09:26:29
 */
class Version20180502092627 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_clacoformbundle_field_choice_category 
            ADD float_value DOUBLE PRECISION DEFAULT NULL, 
            ADD date_value DATETIME DEFAULT NULL, 
            ADD array_value LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
            ADD bool_value TINYINT(1) DEFAULT NULL, 
            CHANGE field_value field_value VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_field_choice_category 
            DROP float_value, 
            DROP date_value, 
            DROP array_value, 
            DROP bool_value, 
            CHANGE field_value field_value VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ');
    }
}
