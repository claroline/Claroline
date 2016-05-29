<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/02/04 03:29:26
 */
class Version20160204152921 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE ujm_object_question (
                question_id INT NOT NULL, 
                ordre INT NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                type LONGTEXT NOT NULL, 
                resourceNode_id INT NOT NULL, 
                INDEX IDX_F91814BFB87FAB32 (resourceNode_id), 
                INDEX IDX_F91814BF1E27F6BF (question_id), 
                PRIMARY KEY(resourceNode_id, question_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_step_question (
                step_id INT NOT NULL, 
                question_id INT NOT NULL, 
                ordre INT NOT NULL, 
                INDEX IDX_D22EA1CE73B21E9C (step_id), 
                INDEX IDX_D22EA1CE1E27F6BF (question_id), 
                PRIMARY KEY(step_id, question_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_step (
                id INT AUTO_INCREMENT NOT NULL, 
                exercise_id INT DEFAULT NULL, 
                value VARCHAR(255) NOT NULL, 
                nbQuestion INT NOT NULL, 
                keepSameQuestion TINYINT(1) DEFAULT NULL, 
                shuffle TINYINT(1) DEFAULT NULL, 
                duration INT NOT NULL, 
                max_attempts INT NOT NULL, 
                ordre INT NOT NULL, 
                INDEX IDX_C2803688E934951A (exercise_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE ujm_picture (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                `label` VARCHAR(255) NOT NULL, 
                url VARCHAR(255) NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                width INT NOT NULL, 
                height INT NOT NULL, 
                INDEX IDX_88AACC8AA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE ujm_object_question 
            ADD CONSTRAINT FK_F91814BFB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_object_question 
            ADD CONSTRAINT FK_F91814BF1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_step_question 
            ADD CONSTRAINT FK_D22EA1CE73B21E9C FOREIGN KEY (step_id) 
            REFERENCES ujm_step (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_step_question 
            ADD CONSTRAINT FK_D22EA1CE1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_step 
            ADD CONSTRAINT FK_C2803688E934951A FOREIGN KEY (exercise_id) 
            REFERENCES ujm_exercise (id) 
            ON DELETE CASCADE
        ');
        $this->addSql('
            ALTER TABLE ujm_picture 
            ADD CONSTRAINT FK_88AACC8AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD anonymous TINYINT(1) DEFAULT NULL, 
            ADD type VARCHAR(255) NOT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            DROP FOREIGN KEY FK_9EBD442FC33F7837
        ');
        $this->addSql('
            DROP INDEX IDX_9EBD442FC33F7837 ON ujm_interaction_graphic
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            DROP width, 
            DROP height, 
            CHANGE document_id picture_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            ADD CONSTRAINT FK_9EBD442FEE45BDBF FOREIGN KEY (picture_id) 
            REFERENCES ujm_picture (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_9EBD442FEE45BDBF ON ujm_interaction_graphic (picture_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_proposal 
            ADD resourceNode_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_proposal 
            ADD CONSTRAINT FK_2672B44BB87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_2672B44BB87FAB32 ON ujm_proposal (resourceNode_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_choice 
            ADD resourceNode_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_choice 
            ADD CONSTRAINT FK_D4BDFA95B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_D4BDFA95B87FAB32 ON ujm_choice (resourceNode_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_paper 
            ADD score DOUBLE PRECISION NOT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_question 
            ADD supplementary LONGTEXT DEFAULT NULL, 
            ADD specification LONGTEXT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_label 
            ADD resourceNode_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_label 
            ADD CONSTRAINT FK_C22A1EB5B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_C22A1EB5B87FAB32 ON ujm_label (resourceNode_id)
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE ujm_step_question 
            DROP FOREIGN KEY FK_D22EA1CE73B21E9C
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            DROP FOREIGN KEY FK_9EBD442FEE45BDBF
        ');
        $this->addSql('
            DROP TABLE ujm_object_question
        ');
        $this->addSql('
            DROP TABLE ujm_step_question
        ');
        $this->addSql('
            DROP TABLE ujm_step
        ');
        $this->addSql('
            DROP TABLE ujm_picture
        ');
        $this->addSql('
            ALTER TABLE ujm_choice 
            DROP FOREIGN KEY FK_D4BDFA95B87FAB32
        ');
        $this->addSql('
            DROP INDEX IDX_D4BDFA95B87FAB32 ON ujm_choice
        ');
        $this->addSql('
            ALTER TABLE ujm_choice 
            DROP resourceNode_id
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP anonymous, 
            DROP type
        ');
        $this->addSql('
            DROP INDEX IDX_9EBD442FEE45BDBF ON ujm_interaction_graphic
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            ADD width INT NOT NULL, 
            ADD height INT NOT NULL, 
            CHANGE picture_id document_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_interaction_graphic 
            ADD CONSTRAINT FK_9EBD442FC33F7837 FOREIGN KEY (document_id) 
            REFERENCES ujm_document (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_9EBD442FC33F7837 ON ujm_interaction_graphic (document_id)
        ');
        $this->addSql('
            ALTER TABLE ujm_label 
            DROP FOREIGN KEY FK_C22A1EB5B87FAB32
        ');
        $this->addSql('
            DROP INDEX IDX_C22A1EB5B87FAB32 ON ujm_label
        ');
        $this->addSql('
            ALTER TABLE ujm_label 
            DROP resourceNode_id
        ');
        $this->addSql('
            ALTER TABLE ujm_paper 
            DROP score
        ');
        $this->addSql('
            ALTER TABLE ujm_proposal 
            DROP FOREIGN KEY FK_2672B44BB87FAB32
        ');
        $this->addSql('
            DROP INDEX IDX_2672B44BB87FAB32 ON ujm_proposal
        ');
        $this->addSql('
            ALTER TABLE ujm_proposal 
            DROP resourceNode_id
        ');
        $this->addSql('
            ALTER TABLE ujm_question 
            DROP supplementary, 
            DROP specification
        ');
    }
}
