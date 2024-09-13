<?php

namespace Claroline\CoreBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/09/14 08:08:40
 */
final class Version20240914080728 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_role CHANGE type entity_type VARCHAR(10) NOT NULL
        ');

        $this->addSql('
            UPDATE claro_role SET entity_type = "platform" WHERE entity_type = "1"
        ');
        $this->addSql('
            UPDATE claro_role SET entity_type = "workspace" WHERE entity_type = "2"
        ');
        $this->addSql('
            UPDATE claro_role SET entity_type = "user" WHERE entity_type = "4"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro_role CHANGE entity_type type INT NOT NULL
        ');
    }
}
