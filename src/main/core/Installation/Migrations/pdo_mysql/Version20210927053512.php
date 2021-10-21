<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/09/27 05:35:39
 */
class Version20210927053512 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_field_facet 
            ADD condition_field VARCHAR(255) DEFAULT NULL, 
            ADD condition_comparator VARCHAR(255) DEFAULT NULL, 
            ADD condition_value LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_field_facet 
            DROP condition_field, 
            DROP condition_comparator, 
            DROP condition_value
        ');
    }
}
