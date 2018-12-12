<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2018/12/06 02:09:43
 */
class Version20181206140941 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE hevinci_learning_objective 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE hevinci_learning_objective SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_10D9D654D17F50A6 ON hevinci_learning_objective (uuid)
        ');
        $this->addSql('
            ALTER TABLE hevinci_scale 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE hevinci_scale SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_D3477F40D17F50A6 ON hevinci_scale (uuid)
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE hevinci_ability SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_11E77B9DD17F50A6 ON hevinci_ability (uuid)
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_ability 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE hevinci_competency_ability SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_38178A41D17F50A6 ON hevinci_competency_ability (uuid)
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_competency 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE hevinci_objective_competency SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_EDBF8544D17F50A6 ON hevinci_objective_competency (uuid)
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE hevinci_competency SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_61ECD5E6D17F50A6 ON hevinci_competency (uuid)
        ');
        $this->addSql('
            ALTER TABLE hevinci_level 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE hevinci_level SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_A5EB96D7D17F50A6 ON hevinci_level (uuid)
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_progress_log 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE hevinci_objective_progress_log SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_F125F347D17F50A6 ON hevinci_objective_progress_log (uuid)
        ');
        $this->addSql('
            ALTER TABLE hevinci_user_progress_log 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE hevinci_user_progress_log SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_5125DF35D17F50A6 ON hevinci_user_progress_log (uuid)
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_progress 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE hevinci_objective_progress SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_CAC2DC38D17F50A6 ON hevinci_objective_progress (uuid)
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability_progress 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE hevinci_ability_progress SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_C8ACD62ED17F50A6 ON hevinci_ability_progress (uuid)
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE hevinci_competency_progress SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_CB827A3D17F50A6 ON hevinci_competency_progress (uuid)
        ');
        $this->addSql('
            ALTER TABLE hevinci_user_progress 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE hevinci_user_progress SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_53E81580D17F50A6 ON hevinci_user_progress (uuid)
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress_log 
            ADD uuid VARCHAR(36) NOT NULL
        ');
        $this->addSql('
            UPDATE hevinci_competency_progress_log SET uuid = (SELECT UUID())
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_8522FF2AD17F50A6 ON hevinci_competency_progress_log (uuid)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP INDEX UNIQ_11E77B9DD17F50A6 ON hevinci_ability
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_61ECD5E6D17F50A6 ON hevinci_competency
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_38178A41D17F50A6 ON hevinci_competency_ability
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_ability 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_10D9D654D17F50A6 ON hevinci_learning_objective
        ');
        $this->addSql('
            ALTER TABLE hevinci_learning_objective 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_A5EB96D7D17F50A6 ON hevinci_level
        ');
        $this->addSql('
            ALTER TABLE hevinci_level 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_EDBF8544D17F50A6 ON hevinci_objective_competency
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_competency 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_D3477F40D17F50A6 ON hevinci_scale
        ');
        $this->addSql('
            ALTER TABLE hevinci_scale 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_C8ACD62ED17F50A6 ON hevinci_ability_progress
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability_progress 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_CB827A3D17F50A6 ON hevinci_competency_progress
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_8522FF2AD17F50A6 ON hevinci_competency_progress_log
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress_log 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_CAC2DC38D17F50A6 ON hevinci_objective_progress
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_progress 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_F125F347D17F50A6 ON hevinci_objective_progress_log
        ');
        $this->addSql('
            ALTER TABLE hevinci_objective_progress_log 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_53E81580D17F50A6 ON hevinci_user_progress
        ');
        $this->addSql('
            ALTER TABLE hevinci_user_progress 
            DROP uuid
        ');
        $this->addSql('
            DROP INDEX UNIQ_5125DF35D17F50A6 ON hevinci_user_progress_log
        ');
        $this->addSql('
            ALTER TABLE hevinci_user_progress_log 
            DROP uuid
        ');
    }
}
