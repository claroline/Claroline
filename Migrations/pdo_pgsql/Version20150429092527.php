<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/29 09:25:28
 */
class Version20150429092527 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_ability_progress (
                id SERIAL NOT NULL, 
                ability_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                passed_activity_ids TEXT DEFAULT NULL, 
                passed_activity_count INT NOT NULL, 
                status VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C8ACD62E8016D8B2 ON hevinci_ability_progress (ability_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_C8ACD62EA76ED395 ON hevinci_ability_progress (user_id)
        ");
        $this->addSql("
            COMMENT ON COLUMN hevinci_ability_progress.passed_activity_ids IS '(DC2Type:simple_array)'
        ");
        $this->addSql("
            CREATE TABLE hevinci_competency_progress (
                id SERIAL NOT NULL, 
                competency_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                level_id INT DEFAULT NULL, 
                type VARCHAR(255) NOT NULL, 
                date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_CB827A3FB9F58C ON hevinci_competency_progress (competency_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CB827A3A76ED395 ON hevinci_competency_progress (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CB827A35FB14BA7 ON hevinci_competency_progress (level_id)
        ");
        $this->addSql("
            ALTER TABLE hevinci_ability_progress 
            ADD CONSTRAINT FK_C8ACD62E8016D8B2 FOREIGN KEY (ability_id) 
            REFERENCES hevinci_ability (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE hevinci_ability_progress 
            ADD CONSTRAINT FK_C8ACD62EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress 
            ADD CONSTRAINT FK_CB827A3FB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress 
            ADD CONSTRAINT FK_CB827A3A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE hevinci_competency_progress 
            ADD CONSTRAINT FK_CB827A35FB14BA7 FOREIGN KEY (level_id) 
            REFERENCES hevinci_level (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE hevinci_ability_progress
        ");
        $this->addSql("
            DROP TABLE hevinci_competency_progress
        ");
    }
}