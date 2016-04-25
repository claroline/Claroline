<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/08/24 04:59:47
 */
class Version20150824165947 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE hevinci_competency CHANGE name name VARCHAR(500) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress_log CHANGE competency_name competency_name VARCHAR(500) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability_progress CHANGE ability_name ability_name VARCHAR(500) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress CHANGE competency_name competency_name VARCHAR(500) NOT NULL
        ');

        // The next two statements were added to circumvent a 1071 mysql error
        // (limited size of index keys). The existing index is dropped then
        // re-created, but only applied to the first 200 characters (so that
        // the column size can be increased further)
        $this->addSql('
            DROP INDEX UNIQ_11E77B9D5E237E06
            ON hevinci_ability
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_11E77B9D5E237E06
            ON hevinci_ability(name(200))
        ');

        $this->addSql('
            ALTER TABLE hevinci_ability CHANGE name name VARCHAR(500) NOT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE hevinci_ability CHANGE name name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability_progress CHANGE ability_name ability_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency CHANGE name name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress CHANGE competency_name competency_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress_log CHANGE competency_name competency_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci
        ');
    }
}
