<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/07/20 09:44:34
 */
class Version20150720094433 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE ujm_step_question (
                step_id INT NOT NULL, 
                question_id INT NOT NULL, 
                position SMALLINT NOT NULL, 
                INDEX IDX_D22EA1CE73B21E9C (step_id), 
                INDEX IDX_D22EA1CE1E27F6BF (question_id), 
                PRIMARY KEY(step_id, question_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE ujm_step_question 
            ADD CONSTRAINT FK_D22EA1CE73B21E9C FOREIGN KEY (step_id) 
            REFERENCES ujm_sequence_step (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_step_question 
            ADD CONSTRAINT FK_D22EA1CE1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_sequence_step CHANGE position position SMALLINT NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE ujm_step_question
        ");
        $this->addSql("
            ALTER TABLE ujm_sequence_step CHANGE position position SMALLINT DEFAULT NULL
        ");
    }
}