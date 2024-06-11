<?php

namespace Innova\PathBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/06/11 05:01:18
 */
final class Version20240611050008 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_step CHANGE step_order entity_order INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE innova_step_secondary_resource CHANGE resource_order entity_order INT NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE innova_step_secondary_resource CHANGE entity_order resource_order INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE innova_step CHANGE entity_order step_order INT NOT NULL
        ');
    }
}
