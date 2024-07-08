<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/07/05 07:58:29
 */
final class Version20240705075828 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX template_unique_name ON claro_template
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            CREATE UNIQUE INDEX claro_template_type_entity_name ON claro_template (
                claro_template_type, entity_name
            )
        ');
    }
}
