<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/08/14 09:58:34
 */
class Version20210814095833 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_field_facet 
            ADD dependency_field VARCHAR(255) NOT NULL, 
            ADD validation_type VARCHAR(255) NOT NULL, 
            ADD comparison_value VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE claro_content_translation CHANGE object_class object_class VARCHAR(191) NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_content_translation CHANGE object_class object_class VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`
        ');
        $this->addSql('
            ALTER TABLE claro_field_facet 
            DROP dependency_field, 
            DROP validation_type, 
            DROP comparison_value
        ');
    }
}
