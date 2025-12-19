<?php

namespace Claroline\OpenBadgeBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2025/12/03 10:13:01
 */
final class Version20251203101259 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE claro__open_badge_badge_organizations (
                badgeclass_id INT NOT NULL, 
                organization_id INT NOT NULL, 
                INDEX IDX_E0DFCCD698CCE3D1 (badgeclass_id), 
                INDEX IDX_E0DFCCD632C8A3DE (organization_id), 
                PRIMARY KEY (badgeclass_id, organization_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_organizations 
            ADD CONSTRAINT FK_E0DFCCD698CCE3D1 FOREIGN KEY (badgeclass_id) 
            REFERENCES claro__open_badge_badge_class (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_organizations 
            ADD CONSTRAINT FK_E0DFCCD632C8A3DE FOREIGN KEY (organization_id) 
            REFERENCES claro__organization (id) 
            ON DELETE CASCADE
        ');

        $this->addSql('
            INSERT INTO claro__open_badge_badge_organizations (badgeclass_id, organization_id)
            SELECT id AS badgeclass_id, issuer_id AS organization_id
            FROM claro__open_badge_badge_class
            WHERE workspace_id IS NULL
        ');

        $this->addSql('
            INSERT INTO claro__open_badge_badge_organizations (badgeclass_id, organization_id)
            SELECT b.id AS badgeclass_id, wo.organization_id AS organization_id
            FROM claro__open_badge_badge_class AS b
            LEFT JOIN workspace_organization AS wo ON (wo.workspace_id = b.workspace_id)
            WHERE b.workspace_id IS NOT NULL
              AND wo.organization_id IS NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_organizations 
            DROP FOREIGN KEY FK_E0DFCCD698CCE3D1
        ');
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_organizations 
            DROP FOREIGN KEY FK_E0DFCCD632C8A3DE
        ');
        $this->addSql('
            DROP TABLE claro__open_badge_badge_organizations
        ');
    }

    public function isTransactional(): bool
    {
        // MySQL/PostgreSQL does not support DDL queries in transactions
        // You can remove this override if your migration does not contain DDL queries
        return false;
    }
}
