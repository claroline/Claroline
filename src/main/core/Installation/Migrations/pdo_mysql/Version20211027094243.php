<?php

namespace Claroline\CoreBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/10/27 09:42:45
 */
class Version20211027094243 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX UNIQ_DFB26A758BED546B ON claro_template
        ');
        $this->addSql('
            ALTER TABLE claro_template CHANGE template_name entity_name VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX template_unique_name ON claro_template (
                claro_template_type, entity_name
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX template_unique_name ON claro_template
        ');
        $this->addSql('
            ALTER TABLE claro_template CHANGE entity_name template_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_DFB26A758BED546B ON claro_template (template_name)
        ');
    }
}
