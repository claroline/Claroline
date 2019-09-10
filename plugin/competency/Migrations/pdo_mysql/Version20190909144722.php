<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2019/09/09 02:47:26
 */
class Version20190909144722 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE hevinci_ability CHANGE name name VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency CHANGE name name VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability_progress CHANGE ability_name ability_name VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress CHANGE competency_name competency_name VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress_log CHANGE competency_name competency_name VARCHAR(255) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE hevinci_ability CHANGE name name VARCHAR(500) NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability_progress CHANGE ability_name ability_name VARCHAR(500) NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency CHANGE name name VARCHAR(500) NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress CHANGE competency_name competency_name VARCHAR(500) NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress_log CHANGE competency_name competency_name VARCHAR(500) NOT NULL COLLATE utf8_unicode_ci
        ');
    }
}
