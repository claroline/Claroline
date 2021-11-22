<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/10/26 05:33:19
 */
class Version20211026053317 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_content_translation CHANGE object_class object_class VARCHAR(191) NOT NULL
        ');
        $this->addSql('
            DROP INDEX template_unique_type ON claro_template_type
        ');
        $this->addSql('
            ALTER TABLE claro_template_type 
            ADD entity_type VARCHAR(255) NOT NULL, 
            CHANGE placeholders placeholders LONGTEXT DEFAULT NULL COMMENT "(DC2Type:json)", 
            CHANGE type_name entity_name VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX template_unique_type ON claro_template_type (entity_name)
        ');
        $this->addSql('
            ALTER TABLE claro_template 
            ADD `system` TINYINT(1) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_template SET `system` = false
        ');

        // Rename templates
        // We cannot do it in an Updater because of the migration on claro_template_type table
        $this->addSql('
            UPDATE claro_template_type SET entity_name = "email_layout" WHERE entity_name = "claro_mail_layout"
        ');
        $this->addSql('
            UPDATE claro_template_type SET entity_name = "user_registration" WHERE entity_name = "claro_mail_registration"
        ');
        $this->addSql('
            UPDATE claro_template_type SET entity_name = "user_email_validation" WHERE entity_name = "claro_mail_validation"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_content_translation CHANGE object_class object_class VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`
        ');
        $this->addSql('
            DROP INDEX template_unique_type ON claro_template_type
        ');
        $this->addSql('
            ALTER TABLE claro_template_type 
            CHANGE entity_name type_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            DROP entity_type, 
            CHANGE placeholders placeholders LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            CREATE UNIQUE INDEX template_unique_type ON claro_template_type (type_name)
        ');

        $this->addSql('
            ALTER TABLE claro_template 
            DROP system
        ');
    }
}
