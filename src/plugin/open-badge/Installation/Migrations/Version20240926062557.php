<?php

namespace Claroline\OpenBadgeBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/09/26 06:26:05
 */
final class Version20240926062557 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_class 
            CHANGE enabled archived TINYINT(1) NOT NULL
        ');

        $this->addSql('
            UPDATE claro__open_badge_badge_class SET archived = !archived 
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_class 
            CHANGE archived enabled TINYINT(1) DEFAULT NULL
        ');
    }
}
