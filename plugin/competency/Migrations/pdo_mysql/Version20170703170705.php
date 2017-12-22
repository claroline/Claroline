<?php

namespace HeVinci\CompetencyBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/07/03 05:07:07
 */
class Version20170703170705 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE hevinci_ability_resource (
                ability_id INT NOT NULL, 
                resourcenode_id INT NOT NULL, 
                INDEX IDX_563CD07E8016D8B2 (ability_id), 
                INDEX IDX_563CD07E77C292AE (resourcenode_id), 
                PRIMARY KEY(ability_id, resourcenode_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE hevinci_competency_resource (
                competency_id INT NOT NULL, 
                resourcenode_id INT NOT NULL, 
                INDEX IDX_922821F3FB9F58C (competency_id), 
                INDEX IDX_922821F377C292AE (resourcenode_id), 
                PRIMARY KEY(competency_id, resourcenode_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability_resource 
            ADD CONSTRAINT FK_563CD07E8016D8B2 FOREIGN KEY (ability_id) 
            REFERENCES hevinci_ability (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability_resource 
            ADD CONSTRAINT FK_563CD07E77C292AE FOREIGN KEY (resourcenode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_resource 
            ADD CONSTRAINT FK_922821F3FB9F58C FOREIGN KEY (competency_id) 
            REFERENCES hevinci_competency (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_resource 
            ADD CONSTRAINT FK_922821F377C292AE FOREIGN KEY (resourcenode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql("
            ALTER TABLE hevinci_ability_progress
            ADD failed_resource_ids LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
            CHANGE passed_activity_ids passed_resource_ids LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
            CHANGE passed_activity_count passed_resource_count INT NOT NULL
        ");
        $this->addSql('
            ALTER TABLE hevinci_ability 
            ADD minResourceCount INT NOT NULL, 
            ADD resourceCount INT NOT NULL, 
            DROP minActivityCount, 
            DROP activityCount
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency CHANGE activitycount resourceCount INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress
            ADD resource_id INT DEFAULT NULL
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            DROP TABLE hevinci_ability_resource
        ');
        $this->addSql('
            DROP TABLE hevinci_competency_resource
        ');
        $this->addSql('
            ALTER TABLE hevinci_ability 
            ADD minActivityCount INT NOT NULL, 
            ADD activityCount INT NOT NULL, 
            DROP minResourceCount, 
            DROP resourceCount
        ');
        $this->addSql("
            ALTER TABLE hevinci_ability_progress
            CHANGE passed_resource_ids passed_activity_ids LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT '(DC2Type:simple_array)',
            CHANGE passed_resource_count passed_activity_count INT NOT NULL,
            DROP failed_resource_ids
        ");
        $this->addSql('
            ALTER TABLE hevinci_competency CHANGE resourcecount activityCount INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE hevinci_competency_progress
            DROP resource_id
        ');
    }
}
