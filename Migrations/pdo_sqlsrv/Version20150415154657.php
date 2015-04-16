<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/04/15 03:46:58
 */
class Version20150415154657 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE hevinci_objective_user (
                objective_id INT NOT NULL, 
                user_id INT NOT NULL, 
                PRIMARY KEY (objective_id, user_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_6D032C1573484933 ON hevinci_objective_user (objective_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6D032C15A76ED395 ON hevinci_objective_user (user_id)
        ");
        $this->addSql("
            CREATE TABLE hevinci_objective_group (
                objective_id INT NOT NULL, 
                group_id INT NOT NULL, 
                PRIMARY KEY (objective_id, group_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_FFDC9E073484933 ON hevinci_objective_group (objective_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FFDC9E0FE54D947 ON hevinci_objective_group (group_id)
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_user 
            ADD CONSTRAINT FK_6D032C1573484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_user 
            ADD CONSTRAINT FK_6D032C15A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_group 
            ADD CONSTRAINT FK_FFDC9E073484933 FOREIGN KEY (objective_id) 
            REFERENCES hevinci_learning_objective (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE hevinci_objective_group 
            ADD CONSTRAINT FK_FFDC9E0FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE hevinci_objective_user
        ");
        $this->addSql("
            DROP TABLE hevinci_objective_group
        ");
    }
}