<?php

namespace Claroline\OpenBadgeBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/09/27 12:39:32
 */
class Version20190927123927 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_class 
            CHANGE description description LONGTEXT DEFAULT NULL, 
            CHANGE criteria criteria LONGTEXT DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro__open_badge_badge_class 
            CHANGE description description LONGTEXT NOT NULL COLLATE utf8_unicode_ci, 
            CHANGE criteria criteria LONGTEXT NOT NULL COLLATE utf8_unicode_ci
        ');
    }
}
