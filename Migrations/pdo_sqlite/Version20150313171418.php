<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/13 05:14:21
 */
class Version20150313171418 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_competency_activity (
                competency_id INTEGER NOT NULL, 
                activity_id INTEGER NOT NULL, 
                PRIMARY KEY(competency_id, activity_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_82CDDCBFFB9F58C ON hevinci_competency_activity (competency_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_82CDDCBF81C06096 ON hevinci_competency_activity (activity_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE hevinci_competency_activity
        ");
    }
}