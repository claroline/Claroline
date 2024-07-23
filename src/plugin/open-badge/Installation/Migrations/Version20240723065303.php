<?php

namespace Claroline\OpenBadgeBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/07/23 06:54:21
 */
final class Version20240723065303 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion 
            DROP image, 
            DROP narrative, 
            DROP revocationReason
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_class 
            CHANGE name entity_name VARCHAR(255) NOT NULL,
            CHANGE created createdAt DATETIME DEFAULT NULL, 
            CHANGE updated updatedAt DATETIME DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence 
            DROP description, 
            DROP genre, 
            DROP audience
        ');

        $this->addSql('
            ALTER TABLE claro__open_badge_evidence CHANGE narrative description LONGTEXT DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_assertion 
            ADD image LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, 
            ADD narrative LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, 
            ADD revocationReason LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_class 
            CHANGE entity_name name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`,
            CHANGE createdAt created DATETIME DEFAULT NULL, 
            CHANGE updatedAt updated DATETIME DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_evidence 
            ADD narrative LONGTEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, 
            ADD genre VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, 
            ADD audience LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`
        ');
    }
}
