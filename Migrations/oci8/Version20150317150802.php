<?php

namespace HeVinci\CompetencyBundle\Migrations\oci8;

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
            ADD (
                activityCount NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE hevinci_ability 
            ADD (
                activityCount NUMBER(10) NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_ability 
            DROP (activityCount)
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency 
            DROP (activityCount)
        ");
    }
}