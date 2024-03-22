<?php

namespace Claroline\CommunityBundle\Installation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2024/02/26 06:46:16
 */
final class Version20240322070000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE claro_tool_mask_decoder SET `name` = "register" WHERE `name` = "create_user"
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
