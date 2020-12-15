<?php

namespace Innova\PathBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2020/07/01 09:02:51
 */
class Version20190821151113 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE innova_step (
                id INT AUTO_INCREMENT NOT NULL, 
                activity_id INT DEFAULT NULL, 
                parameters_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                path_id INT DEFAULT NULL, 
                resource_id INT DEFAULT NULL, 
                activity_height INT DEFAULT NULL, 
                step_order INT NOT NULL, 
                title VARCHAR(255) DEFAULT NULL, 
                description LONGTEXT DEFAULT NULL, 
                numbering VARCHAR(255) DEFAULT NULL, 
                showResourceHeader TINYINT(1) NOT NULL, 
                evaluated TINYINT(1) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                poster VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_86F48567D17F50A6 (uuid), 
                INDEX IDX_86F4856781C06096 (activity_id), 
                INDEX IDX_86F4856788BD9C1F (parameters_id), 
                INDEX IDX_86F48567727ACA70 (parent_id), 
                INDEX IDX_86F48567D96C566B (path_id), 
                INDEX IDX_86F4856789329D25 (resource_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE innova_path (
                id INT AUTO_INCREMENT NOT NULL, 
                structure LONGTEXT DEFAULT NULL, 
                modified TINYINT(1) NOT NULL, 
                numbering VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                manual_progression_allowed TINYINT(1) NOT NULL, 
                show_overview TINYINT(1) DEFAULT '1' NOT NULL, 
                secondaryResourcesTarget VARCHAR(255) DEFAULT '_self' NOT NULL, 
                score_total DOUBLE PRECISION DEFAULT '100' NOT NULL, 
                success_score DOUBLE PRECISION DEFAULT NULL, 
                show_score TINYINT(1) NOT NULL, 
                uuid VARCHAR(36) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_CE19F054D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_CE19F054B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            CREATE TABLE innova_step_secondary_resource (
                id INT AUTO_INCREMENT NOT NULL, 
                step_id INT NOT NULL, 
                resource_id INT NOT NULL, 
                resource_order INT NOT NULL, 
                INDEX IDX_4E895FCB73B21E9C (step_id), 
                INDEX IDX_4E895FCB89329D25 (resource_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE innova_path_progression (
                id INT AUTO_INCREMENT NOT NULL, 
                step_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                progression_status VARCHAR(255) NOT NULL, 
                authorized_access TINYINT(1) NOT NULL, 
                locked_access TINYINT(1) NOT NULL, 
                lockedcall_access TINYINT(1) NOT NULL, 
                INDEX IDX_960F966A73B21E9C (step_id), 
                INDEX IDX_960F966AA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F4856781C06096 FOREIGN KEY (activity_id) 
            REFERENCES claro_activity (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F4856788BD9C1F FOREIGN KEY (parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES innova_step (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F48567D96C566B FOREIGN KEY (path_id) 
            REFERENCES innova_path (id)
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            ADD CONSTRAINT FK_86F4856789329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE innova_path 
            ADD CONSTRAINT FK_CE19F054B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_step_secondary_resource 
            ADD CONSTRAINT FK_4E895FCB73B21E9C FOREIGN KEY (step_id) 
            REFERENCES innova_step (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_step_secondary_resource 
            ADD CONSTRAINT FK_4E895FCB89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_path_progression 
            ADD CONSTRAINT FK_960F966A73B21E9C FOREIGN KEY (step_id) 
            REFERENCES innova_step (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_path_progression 
            ADD CONSTRAINT FK_960F966AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F48567727ACA70
        ');
        $this->addSql('
            ALTER TABLE innova_step_secondary_resource 
            DROP FOREIGN KEY FK_4E895FCB73B21E9C
        ');
        $this->addSql('
            ALTER TABLE innova_path_progression 
            DROP FOREIGN KEY FK_960F966A73B21E9C
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F48567D96C566B
        ');
        $this->addSql('
            DROP TABLE innova_step
        ');
        $this->addSql('
            DROP TABLE innova_path
        ');
        $this->addSql('
            DROP TABLE innova_step_secondary_resource
        ');
        $this->addSql('
            DROP TABLE innova_path_progression
        ');
    }
}
