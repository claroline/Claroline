<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/05/12 08:58:40
 */
class Version20150512085838 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_competency_progress_log (
                id INT AUTO_INCREMENT NOT NULL, 
                competency_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                level_id INT DEFAULT NULL, 
                date DATETIME NOT NULL, 
                percentage INT NOT NULL, 
                INDEX IDX_8522FF2AFB9F58C (competency_id), 
                INDEX IDX_8522FF2AA76ED395 (user_id), 
                INDEX IDX_8522FF2A5FB14BA7 (level_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress_log 
            ADD CONSTRAINT FK_8522FF2AFB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress_log 
            ADD CONSTRAINT FK_8522FF2AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress_log 
            ADD CONSTRAINT FK_8522FF2A5FB14BA7 FOREIGN KEY (level_id) 
            REFERENCES hevinci_level (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress 
            ADD percentage INT NOT NULL, 
            DROP type
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE hevinci_competency_progress_log
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress 
            ADD type VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
            DROP percentage
        ");
    }
}