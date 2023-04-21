<?php

namespace Claroline\OpenBadgeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/04/21 09:10:03
 */
class Version20230421090948 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_rule CHANGE data data LONGTEXT NOT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_endorsement CHANGE claim claim LONGTEXT NOT NULL COMMENT "(DC2Type:json)"
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_verification_object CHANGE allowedOrigins allowedOrigins LONGTEXT NOT NULL COMMENT "(DC2Type:json)"
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_endorsement CHANGE claim claim LONGTEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_rule CHANGE data data LONGTEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_verification_object CHANGE allowedOrigins allowedOrigins LONGTEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT "(DC2Type:json_array)"
        ');
    }
}
