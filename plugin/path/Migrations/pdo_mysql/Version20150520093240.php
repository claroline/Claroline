<?php

namespace Innova\PathBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2015/05/20 01:14:43
 */
class Version20150520093240 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE innova_path (
                id INT AUTO_INCREMENT NOT NULL, 
                structure LONGTEXT NOT NULL, 
                breadcrumbs TINYINT(1) NOT NULL, 
                modified TINYINT(1) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_CE19F054B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE innova_pathtemplate (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                breadcrumbs TINYINT(1) NOT NULL, 
                structure LONGTEXT NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE innova_step (
                id INT AUTO_INCREMENT NOT NULL, 
                activity_id INT DEFAULT NULL, 
                parameters_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                path_id INT DEFAULT NULL, 
                lvl INT NOT NULL, 
                step_order INT NOT NULL, 
                INDEX IDX_86F4856781C06096 (activity_id), 
                INDEX IDX_86F4856788BD9C1F (parameters_id), 
                INDEX IDX_86F48567727ACA70 (parent_id), 
                INDEX IDX_86F48567D96C566B (path_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE innova_step_inherited_resources (
                id INT AUTO_INCREMENT NOT NULL, 
                step_id INT DEFAULT NULL, 
                resource_id INT DEFAULT NULL, 
                lvl INT NOT NULL, 
                INDEX IDX_C7E87ECC73B21E9C (step_id), 
                INDEX IDX_C7E87ECC89329D25 (resource_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE innova_path 
            ADD CONSTRAINT FK_CE19F054B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
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
            ALTER TABLE innova_step_inherited_resources 
            ADD CONSTRAINT FK_C7E87ECC73B21E9C FOREIGN KEY (step_id) 
            REFERENCES innova_step (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE innova_step_inherited_resources 
            ADD CONSTRAINT FK_C7E87ECC89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F48567D96C566B
        ');
        $this->addSql('
            ALTER TABLE innova_step 
            DROP FOREIGN KEY FK_86F48567727ACA70
        ');
        $this->addSql('
            ALTER TABLE innova_step_inherited_resources 
            DROP FOREIGN KEY FK_C7E87ECC73B21E9C
        ');
        $this->addSql('
            DROP TABLE innova_path
        ');
        $this->addSql('
            DROP TABLE innova_pathtemplate
        ');
        $this->addSql('
            DROP TABLE innova_step
        ');
        $this->addSql('
            DROP TABLE innova_step_inherited_resources
        ');
    }
}
