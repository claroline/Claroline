<?php

namespace Icap\BadgeBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/02 04:20:51
 */
class Version20150302162049 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_badge_collection_user_badges (
                badgecollection_id INTEGER NOT NULL, 
                userbadge_id INTEGER NOT NULL, 
                PRIMARY KEY(
                    badgecollection_id, userbadge_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_85F018D4134B8A11 ON claro_badge_collection_user_badges (badgecollection_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_85F018D4DBE73D8B ON claro_badge_collection_user_badges (userbadge_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_badge_collection_user_badges
        ");
    }
}