<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/06/17 11:33:03
 */
final class Version20240617113240 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_type 
            DROP defaultMask
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_resource_type 
            ADD defaultMask INT NOT NULL
        ');
    }
}
