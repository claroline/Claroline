<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/03/25 05:09:55
 */
class Version20150325170954 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_objective_competency (
                id SERIAL NOT NULL, 
                objective_id INT NOT NULL, 
                competency_id INT NOT NULL, 
                level_id INT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF854473484933 ON hevinci_objective_competency (objective_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF8544FB9F58C ON hevinci_objective_competency (competency_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF85445FB14BA7 ON hevinci_objective_competency (level_id)
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF854473484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF8544FB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF85445FB14BA7 FOREIGN KEY (level_id) 
            REFERENCES hevinci_level (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE hevinci_objective_competency
        ");
    }
}