<?php

namespace Claroline\ClacoFormBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/09/20 07:41:45
 */
class Version20210920074139 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX field_unique_name ON claro_clacoformbundle_field
        ');

        $this->addSql('
            UPDATE claro_clacoformbundle_field SET details = "[]" WHERE details IS NULL 
        ');

        // moves props from Field to FieldFacet (this is required because copy did not copy all the props on FieldFacet)
        $this->addSql('
            UPDATE claro_field_facet AS ff
            JOIN claro_clacoformbundle_field AS f ON (ff.id = f.field_facet_id)
            SET 
                ff.uuid = f.uuid,
                ff.is_metadata = f.is_metadata,
                ff.locked = f.locked,
                ff.locked_edition = f.locked_edition,
                ff.hidden = f.hidden,
                ff.options = f.details,
                ff.help = f.help
        ');

        $this->addSql('
            ALTER TABLE claro_clacoformbundle_field 
            DROP field_name, 
            DROP field_type, 
            DROP required, 
            DROP is_metadata, 
            DROP locked, 
            DROP locked_edition, 
            DROP hidden, 
            DROP details, 
            DROP field_order, 
            DROP help,
            DROP uuid
        ');
        $this->addSql('
            CREATE UNIQUE INDEX field_unique_name ON claro_clacoformbundle_field (claco_form_id, field_facet_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX field_unique_name ON claro_clacoformbundle_field
        ');
        $this->addSql('
            ALTER TABLE claro_clacoformbundle_field 
            ADD field_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, 
            ADD field_type INT NOT NULL, 
            ADD required TINYINT(1) NOT NULL, 
            ADD is_metadata TINYINT(1) NOT NULL, 
            ADD locked TINYINT(1) DEFAULT "0" NOT NULL, 
            ADD locked_edition TINYINT(1) DEFAULT "0" NOT NULL, 
            ADD hidden TINYINT(1) DEFAULT "0" NOT NULL, 
            ADD details LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci` COMMENT "(DC2Type:json_array)", 
            ADD field_order INT DEFAULT 1000 NOT NULL, 
            ADD help VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
            ADD uuid VARCHAR(36) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`
        ');
        $this->addSql('
            CREATE UNIQUE INDEX field_unique_name ON claro_clacoformbundle_field (claco_form_id, field_name)
        ');
    }
}
