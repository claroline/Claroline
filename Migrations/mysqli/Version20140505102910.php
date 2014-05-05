<?php

namespace Claroline\ScormBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/05 10:29:14
 */
class Version20140505102910 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_scorm_12 (
                id INT AUTO_INCREMENT NOT NULL, 
                hash_name VARCHAR(50) NOT NULL, 
                mastery_score INT DEFAULT NULL, 
                launch_data VARCHAR(4096) DEFAULT NULL, 
                entry_url VARCHAR(255) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_6FE774D5B87FAB32 (resourceNode_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_scorm_12_info (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                scorm_id INT NOT NULL, 
                score_raw INT DEFAULT NULL, 
                score_min INT DEFAULT NULL, 
                score_max INT DEFAULT NULL, 
                lesson_status VARCHAR(255) DEFAULT NULL, 
                session_time INT DEFAULT NULL, 
                total_time INT DEFAULT NULL, 
                entry VARCHAR(255) DEFAULT NULL, 
                suspend_data VARCHAR(4096) DEFAULT NULL, 
                credit VARCHAR(255) DEFAULT NULL, 
                exit_mode VARCHAR(255) DEFAULT NULL, 
                lesson_location VARCHAR(255) DEFAULT NULL, 
                lesson_mode VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_7D2C37E3A76ED395 (user_id), 
                INDEX IDX_7D2C37E3D75F22BE (scorm_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12 
            ADD CONSTRAINT FK_6FE774D5B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_info 
            ADD CONSTRAINT FK_7D2C37E3A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_12_info 
            ADD CONSTRAINT FK_7D2C37E3D75F22BE FOREIGN KEY (scorm_id) 
            REFERENCES claro_scorm_12 (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_scorm_12_info 
            DROP FOREIGN KEY FK_7D2C37E3D75F22BE
        ");
        $this->addSql("
            DROP TABLE claro_scorm_12
        ");
        $this->addSql("
            DROP TABLE claro_scorm_12_info
        ");
    }
}