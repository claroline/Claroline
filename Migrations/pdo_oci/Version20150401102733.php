<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/01 10:27:35
 */
class Version20150401102733 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            ADD (
                framework_id NUMBER(10) NOT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            ADD CONSTRAINT FK_EDBF854437AECF72 FOREIGN KEY (framework_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_EDBF854437AECF72 ON hevinci_objective_competency (framework_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            DROP (framework_id)
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_competency 
            DROP CONSTRAINT FK_EDBF854437AECF72
        ");
        $this->addSql("
            DROP INDEX IDX_EDBF854437AECF72
        ");
    }
}