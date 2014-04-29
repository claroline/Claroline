<?php

namespace Claroline\ScormBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/29 01:13:27
 */
class Version20140429131325 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_scorm_info (
                id SERIAL NOT NULL, 
                user_id INT NOT NULL, 
                scorm_id INT NOT NULL, 
                score_raw INT DEFAULT NULL, 
                score_min INT DEFAULT NULL, 
                score_max INT DEFAULT NULL, 
                lesson_status VARCHAR(255) DEFAULT NULL, 
                session_time INT DEFAULT NULL, 
                total_time INT DEFAULT NULL, 
                entry VARCHAR(255) DEFAULT NULL, 
                suspend_data VARCHAR(255) DEFAULT NULL, 
                credit VARCHAR(255) DEFAULT NULL, 
                exit_mode VARCHAR(255) DEFAULT NULL, 
                lesson_location VARCHAR(255) DEFAULT NULL, 
                lesson_mode VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_6F4BB916A76ED395 ON claro_scorm_info (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6F4BB916D75F22BE ON claro_scorm_info (scorm_id)
        ");
        $this->addSql("
            CREATE TABLE claro_scorm (
                id SERIAL NOT NULL, 
                hash_name VARCHAR(50) NOT NULL, 
                mastery_score INT DEFAULT NULL, 
                launch_data VARCHAR(255) DEFAULT NULL, 
                entry_url VARCHAR(255) NOT NULL, 
                resourceNode_id INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_B6416871B87FAB32 ON claro_scorm (resourceNode_id)
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_info 
            ADD CONSTRAINT FK_6F4BB916A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm_info 
            ADD CONSTRAINT FK_6F4BB916D75F22BE FOREIGN KEY (scorm_id) 
            REFERENCES claro_scorm (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE claro_scorm 
            ADD CONSTRAINT FK_B6416871B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_scorm_info 
            DROP CONSTRAINT FK_6F4BB916D75F22BE
        ");
        $this->addSql("
            DROP TABLE claro_scorm_info
        ");
        $this->addSql("
            DROP TABLE claro_scorm
        ");
    }
}