<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/11 03:19:59
 */
class Version20150311151958 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_ability_activity (
                ability_id INTEGER NOT NULL, 
                activity_id INTEGER NOT NULL, 
                PRIMARY KEY(ability_id, activity_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_46D92D328016D8B2 ON hevinci_ability_activity (ability_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_46D92D3281C06096 ON hevinci_ability_activity (activity_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE hevinci_ability_activity
        ");
    }
}