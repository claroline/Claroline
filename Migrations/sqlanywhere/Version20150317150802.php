<?php

namespace HeVinci\CompetencyBundle\Migrations\sqlanywhere;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/17 03:08:04
 */
class Version20150317150802 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_competency 
            ADD activityCount INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_ability 
            ADD activityCount INT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_ability 
            DROP activityCount
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP activityCount
        ");
    }
}