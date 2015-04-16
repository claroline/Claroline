<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_sqlite;

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
                objective_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                PRIMARY KEY(objective_id, user_id)
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
                objective_id INTEGER NOT NULL, 
                group_id INTEGER NOT NULL, 
                PRIMARY KEY(objective_id, group_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_FFDC9E073484933 ON hevinci_objective_group (objective_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_FFDC9E0FE54D947 ON hevinci_objective_group (group_id)
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