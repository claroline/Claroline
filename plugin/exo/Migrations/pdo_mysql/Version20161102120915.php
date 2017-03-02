<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2016/11/02 12:09:41
 */
class Version20161102120915 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // Exercises
        $this->addSql('
            UPDATE ujm_exercise SET shuffle=0 WHERE shuffle IS NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_exercise 
            DROP doprint, 
            DROP lock_attempt,
            CHANGE shuffle shuffle TINYINT(1) NOT NULL,
            CHANGE disp_button_interrupt interruptible TINYINT(1) NOT NULL
        ');

        // Steps
        $this->addSql('
            UPDATE ujm_step SET shuffle=0 WHERE shuffle IS NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_step CHANGE title title VARCHAR(255) DEFAULT NULL, 
            CHANGE `value` description LONGTEXT DEFAULT NULL, 
            CHANGE ordre entity_order INT NOT NULL,
            CHANGE shuffle shuffle TINYINT(1) NOT NULL
        ');

        // StepQuestions
        $this->addSql('
            ALTER TABLE ujm_step_question CHANGE ordre entity_order INT NOT NULL
        ');

        // Categories
        $this->addSql('
            ALTER TABLE ujm_category 
            ADD uuid VARCHAR(36) NOT NULL
        ');

        // The new column needs to be filled to be able to add the UNIQUE constraint
        $this->addSql('
            UPDATE ujm_category SET uuid = (SELECT UUID())
        ');

        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_9FDB39F8D17F50A6 ON ujm_category (uuid)
        ');

        // Hints

        // New table for used hints
        $this->addSql('
            CREATE TABLE ujm_answer_hints (
                answer_id INT NOT NULL, 
                hint_id INT NOT NULL, 
                INDEX IDX_70DF26E3AA334807 (answer_id), 
                UNIQUE INDEX UNIQ_70DF26E3519161AB (hint_id), 
                PRIMARY KEY(answer_id, hint_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE ujm_answer_hints 
            ADD CONSTRAINT FK_70DF26E3AA334807 FOREIGN KEY (answer_id) 
            REFERENCES ujm_response (id)
        ');
        $this->addSql('
            ALTER TABLE ujm_answer_hints 
            ADD CONSTRAINT FK_70DF26E3519161AB FOREIGN KEY (hint_id) 
            REFERENCES ujm_hint (id)
        ');

        // New hints properties
        $this->addSql('
            ALTER TABLE ujm_hint 
            CHANGE `value` data LONGTEXT DEFAULT NULL, 
            ADD resourceNode_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE ujm_hint 
            ADD CONSTRAINT FK_B5FFCBE7B87FAB32 FOREIGN KEY (resourceNode_id) 
            REFERENCES claro_resource_node (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_B5FFCBE7B87FAB32 ON ujm_hint (resourceNode_id)
        ');
    }

    public function down(Schema $schema)
    {
        // Downgrades exercises
        $this->addSql('
            ALTER TABLE ujm_exercise 
            ADD doprint TINYINT(1) DEFAULT NULL, 
            ADD lock_attempt TINYINT(1) DEFAULT NULL,
            CHANGE shuffle shuffle TINYINT(1) DEFAULT NULL,
            CHANGE interruptible disp_button_interrupt TINYINT(1) DEFAULT NULL
        ');

        // Steps
        $this->addSql('
            ALTER TABLE ujm_step CHANGE title title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
            CHANGE description `value` TEXT DEFAULT NULL COLLATE utf8_unicode_ci, 
            CHANGE entity_order ordre INT NOT NULL,
            CHANGE shuffle shuffle TINYINT(1) DEFAULT NULL
        ');

        // StepQuestions
        $this->addSql('
            ALTER TABLE ujm_step_question CHANGE entity_order ordre INT NOT NULL
        ');

        // Categories
        $this->addSql('
            DROP INDEX UNIQ_9FDB39F8D17F50A6 ON ujm_category
        ');

        $this->addSql('
            ALTER TABLE ujm_category DROP uuid
        ');

        // Hints
        $this->addSql('
            DROP TABLE ujm_answer_hints
        ');

        $this->addSql('
            ALTER TABLE ujm_hint 
            DROP FOREIGN KEY FK_B5FFCBE7B87FAB32
        ');
        $this->addSql('
            DROP INDEX IDX_B5FFCBE7B87FAB32 ON ujm_hint
        ');
        $this->addSql('
            ALTER TABLE ujm_hint 
            CHANGE data `value` VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci,  
            DROP resourceNode_id
        ');
    }
}
