<?php

namespace Claroline\OpenBadgeBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/05/19 08:47:46
 */
class Version20211012000000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            DELETE FROM claro__open_badge_assertion WHERE revoked = 1 
        ');

        // migrate rules
        $this->addSql('
            UPDATE claro__open_badge_rule 
            SET 
                data = CONCAT("{\"resource\": ", data, ", \"value\": \"passed\"}"),
                action = "resource_status"
            WHERE `action` = "resource_passed"
        ');

        $this->addSql('
            UPDATE claro__open_badge_rule 
            SET 
                data = CONCAT("{\"resource\": ", data, ", \"value\": \"participated\"}"),
                action = "resource_status"
            WHERE `action` = "resource_participated"
        ');

        $this->addSql('
            UPDATE claro__open_badge_rule 
            SET 
                data = CONCAT("{\"workspace\": ", data, ", \"value\": \"passed\"}"),
                action = "workspace_status"
            WHERE `action` = "workspace_passed"
        ');

        $this->addSql('
            UPDATE claro__open_badge_rule 
            SET 
                data = CONCAT("{\"workspace\": ", data, ", \"value\": \"participated\"}"),
                action = "workspace_status"
            WHERE `action` = "workspace_participated"
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
