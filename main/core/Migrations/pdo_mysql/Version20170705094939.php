<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2017/07/05 09:49:43
 */
class Version20170705094939 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE claro_resource_user_evaluation (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_node INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                user_name VARCHAR(255) NOT NULL, 
                evaluation_date DATETIME DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INT DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                score_min DOUBLE PRECISION DEFAULT NULL, 
                score_max DOUBLE PRECISION DEFAULT NULL, 
                custom_score VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_BCA02E7A8A5F48FF (resource_node), 
                INDEX IDX_BCA02E7AA76ED395 (user_id), 
                UNIQUE INDEX resource_user_evaluation (resource_node, user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql("
            CREATE TABLE claro_resource_evaluation (
                id INT AUTO_INCREMENT NOT NULL, 
                resource_user_evaluation INT DEFAULT NULL, 
                evaluation_comment LONGTEXT DEFAULT NULL, 
                more_data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                evaluation_date DATETIME DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INT DEFAULT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                score_min DOUBLE PRECISION DEFAULT NULL, 
                score_max DOUBLE PRECISION DEFAULT NULL, 
                custom_score VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_C2A4B1E7FBE9DF40 (resource_user_evaluation), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            ADD CONSTRAINT FK_BCA02E7A8A5F48FF FOREIGN KEY (resource_node) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE claro_resource_user_evaluation 
            ADD CONSTRAINT FK_BCA02E7AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ');
        $this->addSql('
            ALTER TABLE claro_resource_evaluation 
            ADD CONSTRAINT FK_C2A4B1E7FBE9DF40 FOREIGN KEY (resource_user_evaluation) 
            REFERENCES claro_resource_user_evaluation (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE claro_resource_evaluation 
            DROP FOREIGN KEY FK_C2A4B1E7FBE9DF40
        ');
        $this->addSql('
            DROP TABLE claro_resource_user_evaluation
        ');
        $this->addSql('
            DROP TABLE claro_resource_evaluation
        ');
    }
}
