<?php

namespace Claroline\OpenBadgeBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/09/27 11:57:00
 */
class Version20190927115637 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_class 
            DROP criteria
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_class 
            ADD criteria LONGTEXT NOT NULL COLLATE utf8_unicode_ci
        ');
    }
}
