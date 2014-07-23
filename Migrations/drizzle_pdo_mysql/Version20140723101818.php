<?php

namespace UJM\ExoBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/23 10:18:21
 */
class Version20140723101818 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE ujm_propsal (
                id INT AUTO_INCREMENT NOT NULL, 
                interaction_matching_id INT DEFAULT NULL, 
                `value` TEXT NOT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_B797C100FAB79C10 (interaction_matching_id)
            )
        ");
        $this->addSql("
            CREATE TABLE ujm_interaction_matching (
                id INT AUTO_INCREMENT NOT NULL, 
                interaction_id INT DEFAULT NULL, 
                type_matching_id INT DEFAULT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_AC9801C7886DEE8F (interaction_id), 
                INDEX IDX_AC9801C7F881A129 (type_matching_id)
            )
        ");
        $this->addSql("
            CREATE TABLE ujm_label (
                id INT AUTO_INCREMENT NOT NULL, 
                interaction_matching_id INT DEFAULT NULL, 
                `value` TEXT NOT NULL, 
                score_right_response DOUBLE PRECISION DEFAULT NULL, 
                PRIMARY KEY(id), 
                INDEX IDX_C22A1EB5FAB79C10 (interaction_matching_id)
            )
        ");
        $this->addSql("
            CREATE TABLE ujm_type_matching (
                id INT AUTO_INCREMENT NOT NULL, 
                `value` VARCHAR(255) NOT NULL, 
                code INT NOT NULL, 
                PRIMARY KEY(id), 
                UNIQUE INDEX UNIQ_45333F9A77153098 (code)
            )
        ");
        $this->addSql("
            ALTER TABLE ujm_propsal 
            ADD CONSTRAINT FK_B797C100FAB79C10 FOREIGN KEY (interaction_matching_id) 
            REFERENCES ujm_interaction_matching (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_matching 
            ADD CONSTRAINT FK_AC9801C7886DEE8F FOREIGN KEY (interaction_id) 
            REFERENCES ujm_interaction (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_matching 
            ADD CONSTRAINT FK_AC9801C7F881A129 FOREIGN KEY (type_matching_id) 
            REFERENCES ujm_type_matching (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_label 
            ADD CONSTRAINT FK_C22A1EB5FAB79C10 FOREIGN KEY (interaction_matching_id) 
            REFERENCES ujm_interaction_matching (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_propsal 
            DROP FOREIGN KEY FK_B797C100FAB79C10
        ");
        $this->addSql("
            ALTER TABLE ujm_label 
            DROP FOREIGN KEY FK_C22A1EB5FAB79C10
        ");
        $this->addSql("
            ALTER TABLE ujm_interaction_matching 
            DROP FOREIGN KEY FK_AC9801C7F881A129
        ");
        $this->addSql("
            DROP TABLE ujm_propsal
        ");
        $this->addSql("
            DROP TABLE ujm_interaction_matching
        ");
        $this->addSql("
            DROP TABLE ujm_label
        ");
        $this->addSql("
            DROP TABLE ujm_type_matching
        ");
    }
}