<?php

namespace Claroline\ForumBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2023/02/16 10:26:09
 */
class Version20230228090000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE claro_forum_message SET moderation = "NONE" WHERE moderation = "none"
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
