<?php

namespace HeVinci\CompetencyBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/13 05:14:22
 */
class Version20150313171418 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_competency_activity (
                competency_id INT NOT NULL, 
                activity_id INT NOT NULL, 
                INDEX IDX_82CDDCBFFB9F58C (competency_id), 
                INDEX IDX_82CDDCBF81C06096 (activity_id), 
                PRIMARY KEY(competency_id, activity_id)
            ) COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_activity 
            ADD CONSTRAINT FK_82CDDCBFFB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_activity 
            ADD CONSTRAINT FK_82CDDCBF81C06096 FOREIGN KEY (activity_id) 
            REFERENCES claro_activity (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE hevinci_competency_activity
        ");
    }
}