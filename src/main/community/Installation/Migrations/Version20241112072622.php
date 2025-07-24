<?php

namespace Claroline\CommunityBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/11/12 07:26:24
 */
final class Version20241112072622 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // initialize profile object (only one for now)
        $this->addSql('
            INSERT INTO claro_user_profile (id) VALUE (1)
        ');

        // grabs defined profile sections
        $this->addSql('
            INSERT INTO claro_user_profile_sections (profile_id, section_id)
                SELECT 1 as profile_id, s.id AS section_id
                FROM claro_panel_facet AS s
                LEFT JOIN claro_facet AS f ON (s.facet_id = f.id)
                WHERE s.facet_id IS NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
