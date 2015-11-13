<?php

namespace UJM\ExoBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/10/21 06:44:06
 */
class Version20151021184403 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_exercise_question 
            DROP FOREIGN KEY FK_DB79F2401E27F6BF
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise_question 
            DROP FOREIGN KEY FK_DB79F240E934951A
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise_question 
            ADD CONSTRAINT FK_DB79F2401E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise_question 
            ADD CONSTRAINT FK_DB79F240E934951A FOREIGN KEY (exercise_id) 
            REFERENCES ujm_exercise (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE ujm_subscription 
            DROP FOREIGN KEY FK_A17BA225E934951A
        ");
        $this->addSql("
            ALTER TABLE ujm_subscription 
            ADD CONSTRAINT FK_A17BA225E934951A FOREIGN KEY (exercise_id) 
            REFERENCES ujm_exercise (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_exercise_question 
            DROP FOREIGN KEY FK_DB79F240E934951A
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise_question 
            DROP FOREIGN KEY FK_DB79F2401E27F6BF
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise_question 
            ADD CONSTRAINT FK_DB79F240E934951A FOREIGN KEY (exercise_id) 
            REFERENCES ujm_exercise (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_exercise_question 
            ADD CONSTRAINT FK_DB79F2401E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_subscription 
            DROP FOREIGN KEY FK_A17BA225E934951A
        ");
        $this->addSql("
            ALTER TABLE ujm_subscription 
            ADD CONSTRAINT FK_A17BA225E934951A FOREIGN KEY (exercise_id) 
            REFERENCES ujm_exercise (id)
        ");
    }
}